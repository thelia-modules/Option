<?php

declare(strict_types=1);

namespace Option\Controller\Back;

use Exception;
use Option\Form\CategoryAvailableOptionForm;
use Option\Model\CategoryAvailableOptionQuery;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\OptionProductService;
use Option\Service\OptionService;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Log\Tlog;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Product;
use Thelia\Tools\TokenProvider;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/option/category', name: 'admin_option_category')]
class CategoryAvailableOptionController extends BaseAdminController
{
    #[Route('/show/{categoryId}', name: '_option_category_show', methods: 'GET')]
    public function showCategoryOptionsProduct(int $categoryId, OptionService $optionService): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::VIEW)) {
            return $response;
        }

        $locale = $this->getCurrentEditionLocale();

        $attachedOptions = [];
        foreach (CategoryAvailableOptionQuery::create()->filterByCategoryId($categoryId)->find() as $categoryAvailableOption) {
            $optionProduct = $categoryAvailableOption->getOptionProduct();
            $product = null !== $optionProduct ? $optionProduct->getProduct() : null;
            if (null === $product) {
                continue;
            }

            $product->setLocale($locale);

            $attachedOptions[] = [
                'optionProductId' => $categoryAvailableOption->getOptionId(),
                'productId' => $product->getId(),
                'ref' => $product->getRef(),
                'title' => $product->getTitle(),
                'price' => $optionService->resolveDefaultPrice($product),
            ];
        }

        return $this->render(
            'category/category-option-tab',
            [
                'category_id' => $categoryId,
                'attached_options' => $attachedOptions,
                'form' => $this->createForm(CategoryAvailableOptionForm::class)->createView()->getView(),
            ]
        );
    }

    #[Route('/set', name: '_option_category_set', methods: 'POST')]
    public function setOptionProductOnCategory(Request $request, OptionProductService $optionProductService): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(CategoryAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();

            $category = CategoryQuery::create()->findPk($data['category_id']);
            $optionProductService->setOptionOnCategoryProducts($category, $data['option_id']);

            return $this->generateSuccessRedirect($form);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate product option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process category option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    #[Route('/delete', name: '_option_category_delete', methods: 'POST')]
    public function deleteOptionProductOnCategory(Request $request, OptionProductService $optionProductService, TokenProvider $tokenProvider): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::DELETE)) {
            return $response;
        }

        $tokenProvider->checkToken($request->get('_token'));

        try {
            $optionProductId = $request->get('option_product_id');
            $categoryId = $request->get('category_id');
            $deleteAll = $request->get('delete_all');

            if (!$optionProductId || !$categoryId || $deleteAll === null) {
                return $this->pageNotFound();
            }

            $category = CategoryQuery::create()->findPk($categoryId);
            $optionProductService->deleteOptionOnCategoryTree($category, $optionProductId, $deleteAll);

        } catch (Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/categories/update', [
            "current_tab" => "category_option_tab",
            "category_id" => $categoryId
        ]));
    }

}