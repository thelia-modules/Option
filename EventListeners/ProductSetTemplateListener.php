<?php

namespace Option\EventListeners;

use JsonException;
use Option\Service\OptionProductService;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductSetTemplateEvent;
use Thelia\Core\Event\TheliaEvents;

class ProductSetTemplateListener implements EventSubscriberInterface
{
    private OptionProductService $optionProductService;

    public function __construct(OptionProductService $optionProductService){
        $this->optionProductService = $optionProductService;
    }

    /**
     * @throws PropelException|JsonException
     */
    public function addOptions(ProductSetTemplateEvent $event): void
    {
        $product = $event->getProduct();
        $template = $product->getTemplate();

        $templateOptions = $template->getTemplateAvailableOptions();
        foreach ($templateOptions as $templateOption){
            $this->optionProductService->setOptionOnProduct($product->getId(), $templateOption->getOptionId(), OptionProductService::ADDED_BY_TEMPLATE);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_SET_TEMPLATE => ['addOptions', 50],
        ];
    }
}