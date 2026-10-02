<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartItem;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * Repricing the cart after an add leaves the cart's own line collection alone.
 *
 * A promotion adds its offered line after the module has gone over the cart, and the
 * order is then built from that same Cart object: a collection loaded in between would
 * hand the order a cart without the gift — not ordered, not taken out of stock.
 */
final class CartLinesStayFreshTest extends ActionIntegrationTestCase
{
    public function testALineAddedAfterTheRepricingIsSeenByWhateverReadsTheCartNext(): void
    {
        $currency = $this->factory->currency();
        $category = $this->factory->category();
        $taxRule = $this->factory->taxRule();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 100, 'basePrice' => 10.0]);
        $gift = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 100, 'basePrice' => 10.0]);
        $cart = $this->factory->cart();

        $event = (new CartEvent($cart))
            ->setProductId($product->getId())
            ->setProductSaleElementsId($this->defaultPseId($product->getId()))
            ->setQuantity(1)
            ->setNewness(true)
            ->setAppend(true);
        $this->dispatch($event, TheliaEvents::CART_ADDITEM);

        // Written the way OfferedCartLineService does, on its own instance of the cart.
        (new CartItem())
            ->setCartId($cart->getId())
            ->setProductId($gift->getId())
            ->setProductSaleElementsId($this->defaultPseId($gift->getId()))
            ->setQuantity(1)
            ->setPrice('10')
            ->setPromoPrice('10')
            ->setIsOffered(1)
            ->save();

        self::assertCount(2, $cart->getCartItems());
    }

    private function defaultPseId(int $productId): int
    {
        return (int) ProductSaleElementsQuery::create()
            ->filterByProductId($productId)->filterByIsDefault(true)->findOne()->getId();
    }
}
