<?php

namespace Option\EventListeners;

use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Option\Service\Front\OptionCartItemService;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartItemDuplicationItem;
use Thelia\Core\Event\TheliaEvents;

class DuplicateCartItemListener implements EventSubscriberInterface
{
    private OptionCartItemService $optionCartItemService;

    public function __construct(OptionCartItemService $optionCartItemService)
    {
        $this->optionCartItemService = $optionCartItemService;
    }

    /**
     * @throws PropelException
     */
    public function duplicateOrderProductData(CartItemDuplicationItem $event)
    {
        Propel::disableInstancePooling();
        $options = OptionCartItemCustomizationQuery::create()
            ->filterByCartItemOptionId($event->getOldItem()->getId())
            ->find();

        if ($options->isEmpty()) {
            return;
        }

        $optionsProduct = [];

        /** @var  OptionCartItemCustomization[] $options */
        foreach ($options as $option) {
            $option
                ->setCartItemOptionId($event->getNewItem()->getId())
                ->save();

            $optionsProduct[] = $option->getProductAvailableOption()->getOptionProduct()->getProduct();
        }
        $this->optionCartItemService->handleCartItemOptionPrice($event->getNewItem(), $optionsProduct);
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            TheliaEvents::CART_ITEM_DUPLICATE => ['duplicateOrderProductData', 64]
        );
    }
}