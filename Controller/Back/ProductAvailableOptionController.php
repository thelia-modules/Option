<?php

declare(strict_types=1);

namespace Option\Controller\Back;

use Exception;
use Option\Form\ProductAvailableOptionForm;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\OptionProductService;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\ProductPriceQuery;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Log\Tlog;
use Thelia\Tools\TokenProvider;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/option/product', name: 'admin_option_product')]
class ProductAvailableOptionController extends BaseAdminController
{
    #[Route('/show/{productId}', name: '_option_product_show', methods: 'GET')]
    public function showOptionsProduct(int $productId): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::VIEW)) {
            return $response;
        }

        $locale = $this->getCurrentEditionLocale();

        $attachedOptions = [];
        foreach (ProductAvailableOptionQuery::create()->filterByProductId($productId)->find() as $productAvailableOption) {
            $optionProduct = $productAvailableOption->getOptionProduct();
            $product = null !== $optionProduct ? $optionProduct->getProduct() : null;
            if (null === $product) {
                continue;
            }

            $product->setLocale($locale);

            $productPrice = ProductPriceQuery::create()
                ->filterByProductSaleElements($product->getDefaultSaleElements())
                ->orderByCurrencyId(Criteria::ASC)
                ->findOne();

            $attachedOptions[] = [
                'optionProductId' => $productAvailableOption->getOptionId(),
                'productId' => $product->getId(),
                'ref' => $product->getRef(),
                'title' => $product->getTitle(),
                'price' => null !== $productPrice ? (float) $productPrice->getPrice() : null,
            ];
        }

        return $this->render(
            'product/product-option-tab',
            [
                'product_id' => $productId,
                'attached_options' => $attachedOptions,
                'form' => $this->createForm(ProductAvailableOptionForm::class)->createView()->getView(),
            ]
        );
    }

    #[Route('/set', name: '_option_product_set', methods: 'POST')]
    public function setOptionProduct(OptionProductService $optionProductService): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(ProductAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();

            $optionProductService->setOptionOnProduct($data['product_id'], $data['option_id'], $optionProductService::ADDED_BY_PRODUCT);

            return $this->generateSuccessRedirect($form);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate product option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process product option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    #[Route('/delete', name: '_option_product_delete', methods: 'POST')]
    public function deleteOptionProduct(
        Request       $request,
        OptionProductService $optionProductService,
        TokenProvider $tokenProvider
    ): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::DELETE)) {
            return $response;
        }

        $tokenProvider->checkToken($request->get('_token'));

        try {
            $optionProductId = $request->get('option_product_id');
            $productId = $request->get('product_id');
            $force = $request->get('force');

            if (!$optionProductId || !$productId || $force === null) {
                return $this->pageNotFound();
            }

            $optionProductService->deleteOptionOnProduct($optionProductId, $productId,
                OptionProductService::ADDED_BY_PRODUCT, $force);

        } catch (Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/products/update', [
            "current_tab" => "product_option_tab",
            "product_id" => $productId
        ]));
    }
}