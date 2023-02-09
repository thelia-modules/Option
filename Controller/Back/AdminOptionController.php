<?php

namespace Option\Controller\Back;

use Option\Option;
use Option\Service\BackOffice\OptionProductService;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\Product\ProductUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Exception\TokenAuthenticationException;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Form\Definition\AdminForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\Country;
use Thelia\Model\Currency;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Model\TaxRuleQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\TokenProvider;
use Thelia\Core\HttpFoundation\Response as TheliaResponse;
use Propel\Runtime\Exception\PropelException;

/**
 * @Route("/admin/option", name="admin.option")
 */
class AdminOptionController extends BaseAdminController
{
    /**
     * @Route("/create", name="admin.create.option", methods="POST")
     *
     * @param OptionProductService $optionProductService
     * @param EventDispatcherInterface $dispatcher
     * @param Translator $translator
     * @return Response|null
     */
    public function createOption(OptionProductService $optionProductService, EventDispatcherInterface $dispatcher, Translator $translator): Response|null
    {
        if (null !== $response = $this->checkAuth(AdminResources::PRODUCT, Option::class, AccessManager::CREATE)) {
            return $response;
        }

        $creationForm = $this->createForm(AdminForm::PRODUCT_CREATION);

        try {
            $form = $this->validateForm($creationForm, 'POST');

            $createEvent = $optionProductService->getCreationEvent($form->getData());
            $createEvent->bindForm($form);

            $dispatcher->dispatch($createEvent, TheliaEvents::PRODUCT_CREATE);

            return $this->generateSuccessRedirect($creationForm);

        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $translator->trans('Option creation', [], Option::DOMAIN_NAME),
            $errorMessage,
            $creationForm,
            $ex
        );

        return $this->generateErrorRedirect($creationForm);
    }

    /**
     * @Route("/update", name="admin.update.option", methods="GET")
     *
     * @param ParserContext $parserContext
     * @param Request $request
     * @return string|RedirectResponse|Response|TheliaResponse|null
     * @throws PropelException
     */
    public function updateOption(ParserContext $parserContext, Request $request): string|RedirectResponse|Response|TheliaResponse|null
    {
        if (null !== $response = $this->checkAuth(AdminResources::PRODUCT, Option::class, AccessManager::UPDATE)) {
            return $response;
        }

        if (null === $optionId = $request->get('option_id')) {
            return $response;
        }

        $product = ProductQuery::create()->findOneById($optionId);

        $product?->setLocale($this->getCurrentEditionLocale());

        $changeForm = $this->hydrateDefaultPseForm($parserContext, $product);
        $parserContext->addForm($changeForm);

        return $this->render('edit/option-update', ["option_id" => $optionId]);
    }

    /**
     * @Route("/update/save", name="admin.update.save.option", methods="POST")
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     * @return string|RedirectResponse|Response|TheliaResponse|null
     */
    public function processUpdateOption(EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator): string|RedirectResponse|Response|TheliaResponse|null
    {
        if (null !== $response = $this->checkAuth(AdminResources::PRODUCT, Option::class, AccessManager::UPDATE)) {
            return $response;
        }

        $changeForm = $this->createForm(AdminForm::PRODUCT_MODIFICATION);

        try {
            $form = $this->validateForm($changeForm, 'POST');

            $data = $form->getData();
            $changeEvent = $this->getUpdateEvent($data);
            $changeEvent->bindForm($form);

            $eventDispatcher->dispatch($changeEvent, TheliaEvents::PRODUCT_UPDATE);

            if (!$changeEvent->hasProduct()) {
                throw new \LogicException(
                    $translator->trans('No Option was updated.')
                );
            }

            return $this->generateSuccessRedirect($changeForm);
        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $translator->trans('Option modification'),
            $errorMessage,
            $changeForm,
            $ex
        );

        return $this->generateErrorRedirect($changeForm);
    }

    /**
     * @Route("/delete", name="admin.delete.option", methods={"GET"})
     *
     * @param Request $request
     * @param TokenProvider $tokenProvider
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     * @throws TokenAuthenticationException
     */
    public function deleteOption(Request $request, TokenProvider $tokenProvider, EventDispatcherInterface $eventDispatcher): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::PRODUCT, Option::class, AccessManager::DELETE)) {
            return $response;
        }

        $tokenProvider->checkToken(
            $request->query->get('_token')
        );

        $deleteEvent = new ProductDeleteEvent($request->get('product_id', 0));

        $eventDispatcher->dispatch($deleteEvent, TheliaEvents::PRODUCT_DELETE);

        return $this->generateRedirect('/admin/module/Option');
    }

    /**
     * @param ParserContext $parserContext
     * @param Product $product
     * @return BaseForm
     * @throws PropelException
     */
    private function hydrateDefaultPseForm(ParserContext $parserContext, Product $product): BaseForm
    {
        $saleElement = ProductSaleElementsQuery::create()
            ->filterByProduct($product)
            ->filterByIsDefault(1)
            ->findOne();

        if (!$saleElement) {
            return $this->createForm(AdminForm::PRODUCT_MODIFICATION);
        }

        $defaultCurrency = Currency::getDefaultCurrency();
        $currentCurrency = $this->getCurrentEditionCurrency();

        $productPrice = ProductPriceQuery::create()
            ->filterByCurrency($currentCurrency)
            ->filterByProductSaleElements($saleElement)
            ->findOne();

        if ($productPrice === null) {
            $productPrice = new ProductPrice();

            if ($currentCurrency->getId() != $defaultCurrency->getId()) {
                $productPrice->setFromDefaultCurrency(true);
            }
        }

        if ($productPrice->getFromDefaultCurrency() == true) {
            $this->updatePriceFromDefaultCurrency($productPrice, $saleElement, $defaultCurrency, $currentCurrency);
        }

        $defaultPseData = [
            'product_sale_element_id' => $saleElement->getId(),
            'reference' => $saleElement->getRef(),
            'price' => $this->formatPrice($productPrice->getPrice()),
            'price_with_tax' => $this->formatPrice($this->computePrice($productPrice->getPrice(), $product)),
            'use_exchange_rate' => $productPrice->getFromDefaultCurrency() ? 1 : 0,
            'currency' => $productPrice->getCurrencyId(),
            'weight' => $saleElement->getWeight(),
            'quantity' => $saleElement->getQuantity(),
            'sale_price' => $this->formatPrice($productPrice->getPromoPrice()),
            'sale_price_with_tax' => $this->formatPrice($this->computePrice($productPrice->getPromoPrice(), $product)),
            'onsale' => $saleElement->getPromo() > 0 ? 1 : 0,
            'isnew' => $saleElement->getNewness() > 0 ? 1 : 0,
            'isdefault' => $saleElement->getIsDefault() > 0 ? 1 : 0,
            'ean_code' => $saleElement->getEanCode(),
        ];


        $defaultPseForm = $this->createForm(AdminForm::PRODUCT_DEFAULT_SALE_ELEMENT_UPDATE, FormType::class, $defaultPseData);
        $parserContext->addForm($defaultPseForm);

        $data = [
            'id' => $product->getId(),
            'ref' => $product->getRef(),
            'locale' => $product->getLocale(),
            'title' => $product->getTitle(),
            'chapo' => $product->getChapo(),
            'description' => $product->getDescription(),
            'postscriptum' => $product->getPostscriptum(),
            'visible' => $product->getVisible(),
            'virtual' => $product->getVirtual(),
            'default_category' => $product->getDefaultCategoryId(),
            'brand_id' => $product->getBrandId(),
        ];

        return $this->createForm(AdminForm::PRODUCT_MODIFICATION, FormType::class, $data, []);
    }

    /**
     * @param $formData
     * @return ProductUpdateEvent
     */
    private function getUpdateEvent($formData): ProductUpdateEvent
    {
        $changeEvent = new ProductUpdateEvent($formData['id']);

        $changeEvent
            ->setLocale($formData['locale'])
            ->setRef($formData['ref'])
            ->setTitle($formData['title'])
            ->setChapo($formData['chapo'])
            ->setDescription($formData['description'])
            ->setPostscriptum($formData['postscriptum'])
            ->setVisible($formData['visible'])
            ->setVirtual($formData['virtual'])
            ->setDefaultCategory($formData['default_category'])
            ->setBrandId($formData['brand_id'])
            ->setVirtualDocumentId($formData['virtual_document_id']);

        // Create and dispatch the change event
        return $changeEvent;
    }

    /**
     * @Route("/calculate-raw-price", name="admin.calculate.raw.price.option", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws PropelException
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $return_price = 0;

        $price = (float)($request->query->get('price'));
        $tax_rule_id = (int)($request->query->get('tax_rule'));
        $action = $request->query->get('action');

        $taxRule = TaxRuleQuery::create()->findPk($tax_rule_id);

        if (null !== $price && null !== $taxRule) {
            $calculator = new Calculator();

            $calculator->loadTaxRuleWithoutProduct(
                $taxRule,
                Country::getShopLocation()
            );

            $return_price = $price;

            if ($action == 'to_tax') {
                $return_price = $calculator->getTaxedPrice($price);
            }

            if ($action == 'from_tax') {
                $return_price = $calculator->getUntaxedPrice($price);
            }
        }

        return new JsonResponse(['result' => $this->formatPrice($return_price)]);
    }

    /**
     * Calculate taxed/untexted price for a product.
     *
     * @param $price
     * @param Product $product
     *
     * @return string
     * @throws PropelException
     */
    private function computePrice($price, Product $product): string
    {
        $calc = new Calculator();

        $calc->load(
            $product,
            Country::getShopLocation()
        );

        $return_price = $calc->getTaxedPrice($price);

        return (float)$return_price;
    }

    /**
     * @param $productPrice
     * @param $saleElement
     * @param $defaultCurrency
     * @param $currentCurrency
     * @throws PropelException
     */
    private function updatePriceFromDefaultCurrency($productPrice, $saleElement, $defaultCurrency, $currentCurrency): void
    {
        $priceForDefaultCurrency = ProductPriceQuery::create()
            ->filterByCurrency($defaultCurrency)
            ->filterByProductSaleElements($saleElement)
            ->findOne();

        if ($priceForDefaultCurrency === null) {
            return;
        }

        $productPrice
            ->setPrice($priceForDefaultCurrency->getPrice() * $currentCurrency->getRate())
            ->setPromoPrice($priceForDefaultCurrency->getPromoPrice() * $currentCurrency->getRate());
    }

    /**
     * @param $price
     * @return float
     */
    private function formatPrice($price): float
    {
        return (float)(number_format($price, 6, '.', ''));
    }
}