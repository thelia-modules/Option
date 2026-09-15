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

use Option\Form\TemplateAvailableOptionForm;
use Option\Service\OptionProductService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Log\Tlog;
use Thelia\Model\TemplateQuery;
use Thelia\Tools\TokenProvider;
use Thelia\Tools\URL;

#[Route('/admin/option/template', name: 'admin_option_template')]
class TemplateAvailableOptionController extends BaseAdminController
{
    #[Route('/set', name: '_option_templates_set', methods: 'POST')]
    public function setOptionProductOnTemplate(OptionProductService $optionProductService): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(TemplateAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();
            $template = TemplateQuery::create()->findPk($data['template_id']);
            $optionProductService->setOptionOnTemplateProducts($template, (int) $data['option_id']);

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate template option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process template option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    #[Route('/delete', name: '_option_template_delete', methods: 'POST')]
    public function deleteOptionProductOnTemplate(Request $request, OptionProductService $optionProductService, TokenProvider $tokenProvider): Response
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'Option', AccessManager::DELETE)) {
            return $response;
        }

        $tokenProvider->checkToken($request->get('_token'));

        try {
            $optionProductId = $request->get('option_product_id');
            $templateId = $request->get('template_id');

            if (!$optionProductId || !$templateId) {
                return $this->pageNotFound();
            }

            $template = TemplateQuery::create()->findPk($templateId);
            $optionProductService->deleteOptionOnTemplateProducts($template, (int) $optionProductId);
        } catch (\Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/configuration/templates/update', [
            'current_tab' => 'template_option_tab',
            'template_id' => $templateId,
        ]));
    }
}
