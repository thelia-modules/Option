<?php

namespace Option\EventListeners;

use Option\Event\OptionProductCreateEvent;
use Option\Model\OptionProductQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductCreateEvent;
use Thelia\Core\Event\TheliaEvents;

class ProductCreationListener implements EventSubscriberInterface
{
    public function createOption(ProductCreateEvent $event)
    {
        if (!$event instanceof OptionProductCreateEvent || !$event->isOption()) {
            return null;
        }

        OptionProductQuery::create()
            ->filterByProductId($event->getProduct()->getId())
            ->findOneOrCreate()
            ->save();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_CREATE => ['createOption', 100]
        ];
    }
}