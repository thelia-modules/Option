<?php

namespace Option\EventListeners;

use JsonException;
use Option\Event\OptionProductCreateEvent;
use Option\Model\CategoryAvailableOption;
use Option\Service\OptionProductService;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductCreateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ProductCategoryQuery;

class ProductCreationListener implements EventSubscriberInterface
{
    private OptionProductService $optionProductService;

    public function __construct(OptionProductService $optionProductService)
    {
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

        // Option creation, skip
        if ($newProduct->getOptionProducts()) {
            return;
        }

        $newProductId = $newProduct->getId();
        $template = $newProduct->getTemplate();

        if ($templateOptions = $template?->getTemplateAvailableOptions()) {
            foreach ($templateOptions as $templateOption) {
                $this->optionProductService->setOptionOnProduct(
                    $newProductId,
                    $templateOption->getOptionId(),
                    OptionProductService::ADDED_BY_TEMPLATE
                );
            }
        }

        if (!$category = CategoryQuery::create()->filterById($newProduct->getDefaultCategoryId())->findOne()) {
            return;
        }

        foreach ($this->getCategoryAvailableOptions($category) as $categoryAvailableOption) {
            $this->optionProductService->setOptionOnProduct(
                $newProductId,
                $categoryAvailableOption->getOptionId(),
                OptionProductService::ADDED_BY_CATEGORY
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_CREATE => ['addOptions', 50],
        ];
    }

    /**
     * @return CategoryAvailableOption[] array
     */
    protected function getCategoryAvailableOptions(Category $category): array
    {
        if ($category->getCategoryAvailableOptions()) {
            return iterator_to_array($category->getCategoryAvailableOptions());
        }

        if ($categoryParent = CategoryQuery::create()->filterById($category->getParent())->findOne()) {
            return $this->getCategoryAvailableOptions($categoryParent);
        }

        return [];
    }
}