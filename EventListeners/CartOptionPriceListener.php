<?php

declare(strict_types=1);

namespace Option\EventListeners;

use Option\Service\Front\OptionCartItemService;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\Cart\CartRestoreEvent;
use Thelia\Core\Event\Currency\CurrencyChangeEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Cart;
use Thelia\Model\CartQuery;

/**
 * Puts the options back into the cart line prices after the core has reset them.
 *
 * Thelia\Action\Cart::refreshCartItemPrices() rewrites every line it finds out of step
 * with the catalog, which drops the option supplement without a word and without an
 * event of its own. These are its three ways in:
 *
 *  - a currency change, which a visitor triggers with ?currency= on any URL
 *    (Thelia\Core\EventListener\RequestListener::checkCurrency, no token asked);
 *  - a cart restored from its persistent cookie, refreshed unconditionally;
 *  - settleReservedPrices(), on every add and every quantity change, whenever a
 *    reserved operation is running.
 *
 * Each one is caught below the core's own priority of 128, so the repair runs on lines
 * the core has just finished writing. The repair itself is idempotent, so an event that
 * turned out not to refresh anything costs a read and changes nothing.
 */
final readonly class CartOptionPriceListener implements EventSubscriberInterface
{
    public function __construct(
        private OptionCartItemService $optionCartItemService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::CHANGE_DEFAULT_CURRENCY => ['restoreOnCurrencyChange', 32],
            TheliaEvents::CART_RESTORE_CURRENT => ['restoreOnCartRestore', 32],
            // Below CartItemOptionListener (64), so the line being added already carries
            // the options the visitor picked when the whole cart is gone over.
            TheliaEvents::CART_ADDITEM => ['restoreOnCartChange', 32],
            TheliaEvents::CART_UPDATEITEM => ['restoreOnCartChange', 32],
        ];
    }

    /**
     * @throws PropelException
     */
    public function restoreOnCurrencyChange(CurrencyChangeEvent $event): void
    {
        $session = $event->getRequest()->hasSession() ? $event->getRequest()->getSession() : null;

        if (!$session instanceof Session) {
            return;
        }

        // The cart the core has just repriced is the one whose id sits in the session.
        // Read straight from that id rather than through getSessionCart(), which would
        // dispatch a cart restore in the middle of the currency change.
        $cartId = $session->get(Session::SESSION_CART_ID_NAME);

        $this->restore(null !== $cartId ? CartQuery::create()->findPk($cartId) : null);
    }

    /**
     * @throws PropelException
     */
    public function restoreOnCartRestore(CartRestoreEvent $event): void
    {
        $this->restore($event->getCart());
    }

    /**
     * @throws PropelException
     */
    public function restoreOnCartChange(CartEvent $event): void
    {
        $this->restore($event->getCart());
    }

    /**
     * @throws PropelException
     */
    private function restore(?Cart $cart): void
    {
        if (!$cart instanceof Cart) {
            return;
        }

        foreach ($cart->getCartItems() as $cartItem) {
            $this->optionCartItemService->reconcileCartItemPrice($cartItem);
        }
    }
}
