<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProductQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\SelectedOptionsStore;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Cart;
use Thelia\Model\CartItemQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * The same product added with different options lands on different cart lines; added
 * again with the same options, it lands on the line already carrying them.
 */
final class CartLinePerOptionSetTest extends ActionIntegrationTestCase
{
    public function testTwoDifferentEngravingTextsGiveTwoCartLines(): void
    {
        [$cart, $product, $option] = $this->productWithAnEngravingOption();

        $this->add($cart, $product, [$option->getId() => 'Anna']);
        $this->add($cart, $product, [$option->getId() => 'Bob']);

        $lines = CartItemQuery::create()->filterByCartId($cart->getId())->find();
        self::assertCount(2, $lines, 'two texts, two lines');

        $texts = [];
        foreach ($lines as $line) {
            self::assertSame(1.0, (float) $line->getQuantity());
            $row = OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($line->getId())->findOne();
            $texts[] = json_decode((string) $row?->getCustomizationData(), true)['value'] ?? null;
        }
        sort($texts);
        self::assertSame(['Anna', 'Bob'], $texts);
    }

    public function testTheSameTextTwiceGivesOneLineOfTwo(): void
    {
        [$cart, $product, $option] = $this->productWithAnEngravingOption();

        $this->add($cart, $product, [$option->getId() => 'Anna']);
        $this->add($cart, $product, [$option->getId() => 'Anna']);

        $lines = CartItemQuery::create()->filterByCartId($cart->getId())->find();
        self::assertCount(1, $lines);
        self::assertSame(2.0, (float) $lines->getFirst()->getQuantity());
    }

    public function testTheProductWithoutItsOptionIsALineOfItsOwn(): void
    {
        [$cart, $product, $option] = $this->productWithAnEngravingOption();

        $this->add($cart, $product, [$option->getId() => 'Anna']);
        $this->add($cart, $product, []);
        $this->add($cart, $product, []);

        $lines = CartItemQuery::create()->filterByCartId($cart->getId())->orderById()->find();
        self::assertCount(2, $lines);
        self::assertSame(1.0, (float) $lines[0]->getQuantity());
        self::assertSame(2.0, (float) $lines[1]->getQuantity());
        self::assertSame(0, OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($lines[1]->getId())->count());
    }

    /**
     * @return array{Cart, Product, OptionProduct}
     */
    private function productWithAnEngravingOption(): array
    {
        $currency = $this->factory->currency();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $this->factory->taxRule(), $currency, [
            'baseQuantity' => 100,
            'basePrice' => 10.0,
        ]);
        $optionSource = $this->factory->product($category, $this->factory->taxRule(), $currency, [
            'baseQuantity' => 100,
            'basePrice' => 5.0,
        ]);

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(true);
        $option->save();
        (new ProductAvailableOption())->setProductId($product->getId())->setOptionId($option->getId())->save();

        return [$this->factory->cart(), $product, $option];
    }

    /**
     * @param array<int, string> $options
     */
    private function add(Cart $cart, Product $product, array $options): void
    {
        $pseId = ProductSaleElementsQuery::create()
            ->filterByProductId($product->getId())->filterByIsDefault(true)->findOne()->getId();

        $this->getService(SelectedOptionsStore::class)->set($product->getId(), $options);

        $event = (new CartEvent($cart))
            ->setProductId($product->getId())
            ->setProductSaleElementsId($pseId)
            ->setQuantity(1)
            ->setNewness(true)
            ->setAppend(true);
        $this->dispatch($event, TheliaEvents::CART_ADDITEM);
    }
}
