<?php

namespace Option\EventListeners;

use JsonException;
use Option\Event\OptionProductCreateEvent;
use Option\Service\OptionProductService;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductCreateEvent;
use Thelia\Core\Event\TheliaEvents;

class ProductCreationListener implements EventSubscriberInterface
{
    private OptionProductService $optionProductService;

    public function __construct(OptionProductService $optionProductService){
        $this->optionProductService = $optionProductService;
    }

    /**
     * @throws PropelException|JsonException
     */
    public function addOptions(ProductCreateEvent $event): void
    {
        if (!$event instanceof OptionProductCreateEvent) {
            return;
        }

        $newProduct = $event->getProduct();
        $newProductId = $newProduct->getId();

        $template = $newProduct->getTemplate();
        $templateOptions = $template?->getTemplateAvailableOptions();
        if($templateOptions){
            foreach ($templateOptions as $templateOption){
                $this->optionProductService->setOptionOnProduct($newProductId, $templateOption->getOptionId(), OptionProductService::ADDED_BY_TEMPLATE);
            }
        }

        $productCategories = $newProduct->getCategories();
        if($productCategories) {
            $categoriesOptions = [];
            foreach ($productCategories as $category) {
                $categoriesOptions[] = $category->getCategoryAvailableOptions();
                if($categoriesOptions) {
                    foreach ($categoriesOptions[0] as $categoriesOption) {
                        $this->optionProductService->setOptionOnProduct($newProductId, $categoriesOption->getOptionId
                        (), OptionProductService::ADDED_BY_CATEGORY);
                    }
                }
            }
        }

        ProductAvailableOptionQuery::create()
            ->filterByProductId($newProductId)
            ->findOneOrCreate()
            ->save();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_CREATE => ['addOptions', 50],
        ];
    }
}