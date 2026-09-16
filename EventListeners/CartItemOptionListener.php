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

use Option\Service\CartItemCustomizationOptionHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartItem;

/**
 * Carries the options picked on the product page over to the cart line.
 *
 * Nothing to clean up afterwards: the selection lives in the submitted form, so it dies
 * with the request that carried it.
 */
final readonly class CartItemOptionListener implements EventSubscriberInterface
{
    public function __construct(
        private CartItemCustomizationOptionHandler $handler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Under Thelia\Action\Cart::addItem() (priority 128), which is what puts the cart
        // item on the event: higher up there is no line to attach the options to.
        return [
            TheliaEvents::CART_ADDITEM => ['attachSelectedOptions', 64],
        ];
    }

    public function attachSelectedOptions(CartEvent $event): void
    {
        $cartItem = $event->getCartItem();

        if (!$cartItem instanceof CartItem) {
            return;
        }

        $this->handler->updateCustomizationOptionOnCartItem($cartItem);
    }
}
