<?php

declare(strict_types=1);

namespace Option\Hook\Back;

use Option\Form\TemplateAvailableOptionForm;
use Option\Model\TemplateAvailableOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\Lang;
use Thelia\Model\ProductPriceQuery;

class TemplateEditHook extends BaseHook
{
    public function __construct(
        protected TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'template-edit.bottom' => [
                ['type' => 'back', 'method' => 'onTemplateEditBottom'],
            ],
            'template.edit-js' => [
                ['type' => 'back', 'method' => 'onTemplateEditJs'],
            ],
        ];
    }

    public function onTemplateEditBottom(HookRenderEvent $event): void
    {
        $templateId = (int) $event->getArgument('template_id');
        $locale = $this->getCurrentLocale();

        $attachedOptions = [];
        foreach (TemplateAvailableOptionQuery::create()->filterByTemplateId($templateId)->find() as $templateAvailableOption) {
            $optionProduct = $templateAvailableOption->getOptionProduct();
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
                'optionProductId' => $templateAvailableOption->getOptionId(),
                'productId' => $product->getId(),
                'ref' => $product->getRef(),
                'title' => $product->getTitle(),
                'price' => null !== $productPrice ? (float) $productPrice->getPrice() : null,
            ];
        }

        $event->add($this->render(
            'Option/template/template-edit.bottom.html.twig',
            [
                'template_id' => $templateId,
                'attached_options' => $attachedOptions,
                'form' => $this->formFactory->createForm(TemplateAvailableOptionForm::getName(), FormType::class)->createView()->getView(),
            ]
        ));
    }

    public function onTemplateEditJs(HookRenderEvent $event): void
    {
        $event->add($this->render('Option/template/template-edit.js.html.twig'));
    }

    private function getCurrentLocale(): string
    {
        $session = $this->getRequest()?->getSession();
        if ($session instanceof Session) {
            return $session->getAdminEditionLang()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }
}
