<?php

namespace Option\Controller\Back;

use Option\Form\ProductAvailableOptionForm;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\BackOffice\OptionProductService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;
use Thelia\Core\HttpFoundation\Response as TheliaResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/option/product", name="admin.option.product")
 */
class ProductAvailableOptionController extends BaseAdminController
{
    /**
     * @Route("/show/{productId}", name="admin.option.product.show")
     *
     * @param $productId
     * @return string|RedirectResponse|Response|TheliaResponse
     */
    public function showOptionsProduct($productId): string|RedirectResponse|Response|TheliaResponse
    {
        return $this->render(
            'product/product-option-tab',
            [
                'product_id' => $productId
            ]
        );
    }

    /**
     * @Route("/set", name="admin.option.product.set", methods="POST")
     *
     * @return Response|null
     */
    public function setOptionProduct(OptionProductService $optionProductService): Response
    {
        $form = $this->createForm(ProductAvailableOptionForm::getName());
        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();

            $optionProductService->setOptionProduct($data['product_id'], $data['option_id']);

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate product option form: $errorMessage");
        }

        if (false !== $errorMessage) {
            $this->setupFormErrorContext(
                'Failed to process product option tab form data',
                $errorMessage,
                $form
            );
        }
        return $this->generateErrorRedirect($form);
    }

    /**
     * @Route("/delete", name="admin.option.product.delete")
     *
     * @return Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function deleteOptionsProduct()
    {
        $request = $this->getRequest();
        $optionProductId = $request->get('option_product_id');
        $productId = $request->get('product_id');

        ProductAvailableOptionQuery::create()
            ->filterByOptionId($optionProductId)
            ->filterByProductId($productId)
            ->delete();

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/products/update', [
            "current_tab" => "product_option_tab",
            "product_id" => $productId
        ]));
    }
}