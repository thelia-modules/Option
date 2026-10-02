<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Option\Controller\Back;

use Option\Form\ProductAvailableOptionForm;
use Option\Option;
use Option\Service\Back\OptionTabContextService;
use Option\Service\OptionProductService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Log\Tlog;
use Thelia\Tools\TokenProvider;
use Thelia\Tools\URL;

#[Route('/admin/option/product', name: 'admin_option_product')]
class ProductAvailableOptionController extends BaseAdminController
{
    #[Route('/show/{productId}', name: '_option_product_show', methods: 'GET')]
    public function showOptionsProduct(OptionTabContextService $tabContext, int $productId): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::VIEW)) {
            return $response;
        }

        return $this->render(
            'product/product-option-tab',
            [
                'product_id' => $productId,
                'attached_options' => $tabContext->attachedToProduct($productId),
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

            $optionProductService->setOptionOnProduct(
                (int) $data['product_id'],
                (int) $data['option_id'],
                OptionProductService::ADDED_BY_PRODUCT,
            );

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $ex) {
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
        Request $request,
        OptionProductService $optionProductService,
        TokenProvider $tokenProvider,
    ): Response {
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

            $optionProductService->deleteOptionOnProduct(
                (int) $optionProductId,
                (int) $productId,
                OptionProductService::ADDED_BY_PRODUCT,
                (bool) $force,
            );
        } catch (\Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/products/update', [
            'product_id' => $productId,
            'current_tab' => Option::PRODUCT_OPTION_TAB_ID,
        ]));
    }
}
