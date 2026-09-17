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

namespace Option\EventListeners;

use Option\Model\OptionCartItemOrderProductQuery;
use Option\Service\Front\OptionOrderProductService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\OrderProduct;

class OrderListener implements EventSubscriberInterface
{
    protected OptionOrderProductService $customizationOrderProductService;

    public function __construct(OptionOrderProductService $customizationOrderProductService)
    {
        $this->customizationOrderProductService = $customizationOrderProductService;
    }

    public function handleCustomization(OrderEvent $event): void
    {
        $placedOrder = $event->getOrder();
        if (!$placedOrder) {
            return;
        }

        $orderProducts = $placedOrder->getOrderProducts();

        /** @var OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            $this->setOrderProductData($orderProduct);
            $this->customizationOrderProductService->handleOrderProduct($orderProduct);
        }
    }

    protected function setOrderProductData(OrderProduct $orderProduct): void
    {
        $cartItemId = $orderProduct->getCartItemId();

        if (null === $cartItemId) {
            return;
        }

        $optionCartItemOrderProducts = OptionCartItemOrderProductQuery::create()
            ->filterByCartItemOptionId($cartItemId)
            ->find();

        foreach ($optionCartItemOrderProducts as $optionCartItemOrderProduct) {
            $optionCartItemOrderProduct
                ->setOrderProductId($orderProduct->getId())
                ->save();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::ORDER_BEFORE_PAYMENT => ['handleCustomization', 200],
        ];
    }
}
