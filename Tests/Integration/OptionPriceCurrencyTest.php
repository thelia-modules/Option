<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProductQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\SelectedOptionsStore;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductPrice;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * An option is priced in the currency of the cart, like the line it hangs under.
 */
final class OptionPriceCurrencyTest extends ActionIntegrationTestCase
{
    public function testTheOptionIsPricedInTheCartCurrency(): void
    {
        $eur = $this->factory->currency();
        $usd = $this->factory->currency(['code' => 'USD', 'symbol' => '$', 'rate' => 2.0]);
        $category = $this->factory->category();
        $taxRule = $this->factory->taxRule();
        $product = $this->factory->product($category, $taxRule, $eur, ['baseQuantity' => 100, 'basePrice' => 10.0]);
        $optionSource = $this->factory->product($category, $taxRule, $eur, ['baseQuantity' => 100, 'basePrice' => 5.0]);

        $optionPse = ProductSaleElementsQuery::create()
            ->filterByProductId($optionSource->getId())->filterByIsDefault(true)->findOne();
        (new ProductPrice())
            ->setProductSaleElementsId($optionPse->getId())
            ->setCurrencyId($usd->getId())
            ->setPrice('30')
            ->setPromoPrice('30')
            // An explicit USD price: left to its default, the row asks to be converted
            // from the default currency instead, which is how the core reads it too.
            ->setFromDefaultCurrency(false)
            ->save();
        $hostPse = ProductSaleElementsQuery::create()
            ->filterByProductId($product->getId())->filterByIsDefault(true)->findOne();
        (new ProductPrice())
            ->setProductSaleElementsId($hostPse->getId())
            ->setCurrencyId($usd->getId())
            ->setPrice('20')
            ->setPromoPrice('20')
            ->setFromDefaultCurrency(false)
            ->save();

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(false);
        $option->save();
        (new ProductAvailableOption())->setProductId($product->getId())->setOptionId($option->getId())->save();

        $cart = $this->factory->cart(null, ['currency' => $usd]);
        $this->getService(SelectedOptionsStore::class)->set($product->getId(), [$option->getId() => true]);

        $event = (new CartEvent($cart))
            ->setProductId($product->getId())
            ->setProductSaleElementsId($hostPse->getId())
            ->setQuantity(1)
            ->setNewness(true)
            ->setAppend(true);
        $this->dispatch($event, TheliaEvents::CART_ADDITEM);

        $line = CartItemQuery::create()->filterByCartId($cart->getId())->findOne();
        $row = OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($line->getId())->findOne();

        self::assertNotNull($row, 'the option is attached');
        self::assertEqualsWithDelta(30.0, (float) $row->getPrice(), 0.01, 'option priced in USD (30), not in the first price row');
        self::assertEqualsWithDelta(50.0, (float) $line->getPrice(), 0.01, 'USD line 20 + option 30');
    }

    public function testAnOptionWithoutAPriceInTheCartCurrencyIsConvertedAtItsRate(): void
    {
        $eur = $this->factory->currency();
        $usd = $this->factory->currency(['code' => 'USD', 'symbol' => '$', 'rate' => 2.0]);
        $category = $this->factory->category();
        $taxRule = $this->factory->taxRule();
        $product = $this->factory->product($category, $taxRule, $eur, ['baseQuantity' => 100, 'basePrice' => 10.0]);
        $optionSource = $this->factory->product($category, $taxRule, $eur, ['baseQuantity' => 100, 'basePrice' => 5.0]);
        $hostPse = ProductSaleElementsQuery::create()
            ->filterByProductId($product->getId())->filterByIsDefault(true)->findOne();

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(false);
        $option->save();
        (new ProductAvailableOption())->setProductId($product->getId())->setOptionId($option->getId())->save();

        $cart = $this->factory->cart(null, ['currency' => $usd]);
        $this->getService(SelectedOptionsStore::class)->set($product->getId(), [$option->getId() => true]);

        $event = (new CartEvent($cart))
            ->setProductId($product->getId())
            ->setProductSaleElementsId($hostPse->getId())
            ->setQuantity(1)
            ->setNewness(true)
            ->setAppend(true);
        $this->dispatch($event, TheliaEvents::CART_ADDITEM);

        $line = CartItemQuery::create()->filterByCartId($cart->getId())->findOne();
        $row = OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($line->getId())->findOne();

        self::assertEqualsWithDelta(10.0, (float) $row->getPrice(), 0.01, '5 EUR at a rate of 2');
        self::assertEqualsWithDelta(30.0, (float) $line->getPrice(), 0.01, 'host 20 USD (converted) + option 10');
    }
}
