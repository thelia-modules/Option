<?php

declare(strict_types=1);

namespace Option\Controller\Back;

use Exception;
use Option\Model\OptionProductQuery;
use Option\Option;
use Option\Service\OptionConfigurationPresenter;
use Option\Service\OptionService as OptionService;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\CurrencyQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Exception\TokenAuthenticationException;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Option\Form\OptionCreationForm;
use Option\Form\OptionModificationForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\Country;
use Thelia\Model\ProductQuery;
use Thelia\Model\TaxRuleQuery;
use Thelia\Domain\Taxation\TaxEngine\Calculator;
use Thelia\Tools\TokenProvider;

#[Route('/admin/option', name: 'admin_option')]
class OptionController extends BaseAdminController
{
    /**
     * Standalone module page: same content as the module-configuration hook, but under
     * /admin/option so the side-nav highlights the Options entry alone (the Modules
     * section reacts to any /admin/module/* path).
     *
     * @throws PropelException
     */
    #[Route('', name: '_config_view', methods: 'GET')]
    public function configurationView(OptionConfigurationPresenter $configurationPresenter): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::VIEW)) {
            return $response;
        }

        return $this->render(
            'configuration/option-configuration-page',
            $configurationPresenter->present()
        );
    }

    #[Route('/create', name: '_create_option', methods: 'POST')]
    public function createOption(
        OptionService $optionService,
        Translator    $translator
    ): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::CREATE)) {
            return $response;
        }

        $creationForm = $this->createForm(OptionCreationForm::class);

        try {
            $optionService->createOption($this->validateForm($creationForm, 'POST'));

            return $this->generateSuccessRedirect($creationForm);

        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (Exception $ex) {
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
     * @throws PropelException
     */
    #[Route('/update', name: '_update_option_view', methods: 'GET')]
    public function updateOptionView(Request $request, OptionService $optionService): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::UPDATE)) {
            return $response;
        }

        if (!$optionId = $request->get('option_id')) {
            return $this->pageNotFound();
        }

        // Only option-products may be edited through this screen.
        if (null === OptionProductQuery::create()->filterByProductId((int) $optionId)->findOne()) {
            return $this->pageNotFound();
        }

        if (!$product = ProductQuery::create()->findPk((int) $optionId)) {
            return $this->pageNotFound();
        }

        $locale = $this->getCurrentEditionLocale();
        $product->setLocale($locale);

        $defaultCurrency = CurrencyQuery::create()->filterByByDefault(1)->findOne();

        return $this->render(
            'edit/option-update',
            [
                'option_id' => (int) $optionId,
                'form' => $this->createForm(OptionModificationForm::class)->createView()->getView(),
                'locale' => $locale,
                'option' => [
                    'id' => $product->getId(),
                    'ref' => $product->getRef(),
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'chapo' => $product->getChapo(),
                    'postscriptum' => $product->getPostscriptum(),
                    'visible' => (bool) $product->getVisible(),
                    'virtual' => (bool) $product->getVirtual(),
                    'defaultCategory' => $product->getDefaultCategoryId(),
                    // Current brand: submitted back unchanged so saving the General tab does
                    // not wipe the product's brand association (setBrandId is applied on update).
                    'brandId' => $product->getBrandId(),
                ],
                'price' => $optionService->resolveDefaultPrice($product),
                'currency_symbol' => null !== $defaultCurrency ? $defaultCurrency->getSymbol() : '',
            ]
        );
    }

    #[Route('/update', name: '_update_option_process', methods: 'POST')]
    public function updateOptionProcess(
        TranslatorInterface $translator,
        OptionService       $optionService,
    ): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::UPDATE)) {
            return $response;
        }

        $changeForm = $this->createForm(OptionModificationForm::class);

        try {
            $form = $this->validateForm($changeForm, 'POST');

            // Only option-products may be updated through this screen: reject a forged id
            // pointing at any other catalog product.
            if (null === OptionProductQuery::create()->filterByProductId((int) $form->get('id')->getData())->findOne()) {
                return $this->pageNotFound();
            }

            $optionService->updateOption($form);

            return $this->generateSuccessRedirect($changeForm);
        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $translator->trans('Option modification', [], Option::DOMAIN_NAME),
            $errorMessage,
            $changeForm,
            $ex
        );

        return $this->generateErrorRedirect($changeForm);
    }

    /**
     * @throws TokenAuthenticationException
     */
    #[Route('/delete', name: '_delete_option', methods: 'POST')]
    public function deleteOption(
        Request       $request,
        TokenProvider $tokenProvider,
        OptionService $optionService
    ): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::DELETE)) {
            return $response;
        }

        $tokenProvider->checkToken(
            $request->query->get('_token')
        );

        $productId = (int) $request->get('product_id');

        // Only option-products may be deleted through this screen: reject a forged id
        // pointing at any other catalog product.
        if (null === OptionProductQuery::create()->filterByProductId($productId)->findOne()) {
            return $this->pageNotFound();
        }

        $optionService->deleteOption($productId);

        return $this->generateRedirect('/admin/option');
    }

    /**
     * @throws PropelException
     */
    #[Route('/calculate-raw-price', name: '_calculate_raw_price_option', methods: 'GET')]
    public function calculatePrice(Request $request): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::VIEW)) {
            return $response;
        }

        $price = (float)($request->query->get('price'));
        $tax_rule_id = (int)($request->query->get('tax_rule'));
        $action = $request->query->get('action');

        $taxRule = TaxRuleQuery::create()->findPk($tax_rule_id);

        if (!$price || !$taxRule) {
            return new JsonResponse(
                [
                    'result' => (float)number_format(0, 6, '.', '')
                ]
            );
        }

        $calculator = new Calculator();

        $calculator->loadTaxRuleWithoutProduct(
            $taxRule,
            Country::getShopLocation()
        );

        $return_price = $price;

        if ($action === 'to_tax') {
            $return_price = $calculator->getTaxedPrice($price);
        }

        if ($action === 'from_tax') {
            $return_price = $calculator->getUntaxedPrice($price);
        }

        return new JsonResponse(
            [
                'result' => (float)number_format($return_price, 6, '.', '')
            ]
        );
    }
}