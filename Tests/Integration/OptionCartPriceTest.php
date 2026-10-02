<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\OptionCartItemService;
use Option\Service\Front\OptionOrderProductService;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\Currency\CurrencyChangeEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\CartItemQuery;
use Thelia\Model\Currency;
use Thelia\Model\Order;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductQuery;
use Thelia\Model\OrderProductTax;
use Thelia\Model\OrderProductTaxQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * An option is paid for on top of the catalogue price of the line it sits under.
 *
 * The amount lives in option_cart_item_order_product. In the cart it is reported into
 * cart_item.price, because the cart total is built from that column alone; in the order
 * it becomes a line of its own and is taken off the host line. The same two figures serve
 * both, and nothing recomputes them, which is what keeps the two totals equal.
 */
final class OptionCartPriceTest extends ActionIntegrationTestCase
{
    private const CATALOG_PRICE = 100.0;

    private const OPTION_PRICE = 15.0;

    /**
     * Untaxed and taxed amounts frozen on the option row under the host product's rule,
     * a 10% one. The shop-wide customisation rule is a different one on purpose: a figure
     * worked out again from the taxed amount could not land on this number.
     */
    private const FROZEN_UNTAXED = 10.0;

    private const FROZEN_TAXED = 11.0;

    private const HOST_TAX = 20.0;

    public function testTheOptionIsInTheLinePriceOnceAttached(): void
    {
        [$cartItem, $service] = $this->cartLineWithOneOption();

        self::assertSame(
            self::CATALOG_PRICE + self::OPTION_PRICE,
            (float) $cartItem->getPrice(),
            'the line carries catalogue + option',
        );
        self::assertSame(self::OPTION_PRICE, $service->optionSupplement($cartItem));
    }

    public function testACurrencyChangeDoesNotGiveTheOptionAway(): void
    {
        [$cartItem] = $this->cartLineWithOneOption();

        $this->changeCurrency($cartItem->getCart());

        self::assertSame(
            self::CATALOG_PRICE + self::OPTION_PRICE,
            (float) $this->reread($cartItem)->getPrice(),
            'the option survives the core price refresh',
        );
    }

    public function testAttachingTheSameOptionTwiceDoesNotStackItsPrice(): void
    {
        [$cartItem, $service, , $optionProduct] = $this->cartLineWithOneOption();

        $service->persistCartItemCustomizationData($cartItem, $optionProduct, []);
        $service->handleCartItemOptionPrice($cartItem);

        self::assertSame(
            self::CATALOG_PRICE + self::OPTION_PRICE,
            (float) $cartItem->getPrice(),
            'the option is charged once, not twice',
        );
    }

    /**
     * An option whose catalogue entry is gone leaves a row naming nothing. Read without a
     * guard it throws on ORDER_BEFORE_PAYMENT, after the order is committed and before
     * the payment is called, which leaves an order nobody paid for and a blank page.
     */
    public function testAnOptionRemovedFromTheCatalogueDropsItsLineNotTheOrder(): void
    {
        [$orderProduct, $service] = $this->orderLineWithOneOptionRow(nameable: false);

        $service->handleOrderProduct($orderProduct);

        self::assertSame(
            self::CATALOG_PRICE,
            (float) $orderProduct->getPrice(),
            'nothing is taken off the host line for an option that can no longer be named',
        );
        self::assertCount(0, $this->optionLinesOf($orderProduct), 'no nameless option line is invoiced');
    }

    /**
     * The two figures were settled under the host product's tax rule when the visitor
     * picked the option. Working them out again from the shop-wide customisation rule
     * would take an amount off the host line that was never added to it.
     */
    public function testTheOptionLineCopiesTheAmountsFrozenAtTheCart(): void
    {
        [$orderProduct, $service] = $this->orderLineWithOneOptionRow(nameable: true);

        $service->handleOrderProduct($orderProduct);

        $optionLines = $this->optionLinesOf($orderProduct);
        self::assertCount(1, $optionLines, 'the option is invoiced on a line of its own');

        $optionLine = $optionLines->getFirst();
        self::assertSame(self::FROZEN_UNTAXED, (float) $optionLine->getPrice(), 'the frozen untaxed amount, untouched');
        self::assertSame(
            self::FROZEN_TAXED - self::FROZEN_UNTAXED,
            (float) OrderProductTaxQuery::create()->filterByOrderProductId($optionLine->getId())->findOne()?->getAmount(),
            'the VAT is the gap between the two frozen figures',
        );

        self::assertSame(
            self::CATALOG_PRICE - self::FROZEN_UNTAXED,
            (float) $orderProduct->getPrice(),
            'the host line gives up exactly what the option line takes',
        );
        self::assertSame(
            self::HOST_TAX - (self::FROZEN_TAXED - self::FROZEN_UNTAXED),
            (float) OrderProductTaxQuery::create()->filterByOrderProductId($orderProduct->getId())->findOne()?->getAmount(),
            'and exactly the VAT that went with it',
        );
    }

    /**
     * The subtraction from the host line is absolute, not replayable.
     */
    public function testHandlingTheSameOrderLineTwiceInvoicesTheOptionOnce(): void
    {
        [$orderProduct, $service] = $this->orderLineWithOneOptionRow(nameable: true);

        $service->handleOrderProduct($orderProduct);
        $service->handleOrderProduct($orderProduct);

        self::assertCount(1, $this->optionLinesOf($orderProduct), 'one option, one line');
        self::assertSame(
            self::CATALOG_PRICE - self::FROZEN_UNTAXED,
            (float) $orderProduct->getPrice(),
            'the host line is charged for the option once',
        );
    }

    /**
     * @return ObjectCollection<OrderProduct>
     */
    private function optionLinesOf(OrderProduct $host): ObjectCollection
    {
        return OrderProductQuery::create()
            ->filterByOrderId($host->getOrderId())
            ->filterByProductRef('Personalisation')
            ->find();
    }

    /**
     * @return array{0: OrderProduct, 1: OptionOrderProductService}
     */
    private function orderLineWithOneOptionRow(bool $nameable): array
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();

        $product = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 100,
            'basePrice' => self::CATALOG_PRICE,
        ]);

        $orderProduct = $this->orderProductFor($this->factory->order(), $product);

        $optionRow = (new OptionCartItemOrderProduct())
            ->setOrderProductId($orderProduct->getId())
            ->setPrice((string) self::FROZEN_UNTAXED)
            ->setTaxedPrice((string) self::FROZEN_TAXED)
            ->setQuantity('1');

        if ($nameable) {
            $optionSource = $this->factory->product($category, $taxRule, $currency, [
                'baseQuantity' => 100,
                'basePrice' => self::OPTION_PRICE,
            ]);

            $optionProduct = (new OptionProduct())
                ->setProductId($optionSource->getId())
                ->setIsCustomizable(false);
            $optionProduct->save();

            $available = (new ProductAvailableOption())
                ->setProductId($product->getId())
                ->setOptionId($optionProduct->getId());
            $available->save();

            $optionRow->setProductAvailableOptionId($available->getId());
        }

        $optionRow->save();

        // What ON DELETE SET NULL leaves behind once the option is gone from the catalogue.
        self::assertSame(
            $nameable,
            null !== OptionCartItemOrderProductQuery::create()->findPk($optionRow->getId())?->getProductAvailableOptionId(),
        );

        /** @var OptionOrderProductService $service */
        $service = $this->getService(OptionOrderProductService::class);

        return [$orderProduct, $service];
    }

    private function orderProductFor(Order $order, Product $product): OrderProduct
    {
        $pse = $this->defaultPseFor($product);

        $orderProduct = (new OrderProduct())
            ->setOrderId($order->getId())
            ->setProductRef($product->getRef())
            ->setProductSaleElementsRef($pse->getRef())
            ->setProductSaleElementsId($pse->getId())
            ->setTitle($product->getRef())
            ->setQuantity(1)
            ->setPrice((string) self::CATALOG_PRICE)
            ->setPromoPrice((string) self::CATALOG_PRICE)
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setVirtual(0);
        $orderProduct->save();

        // Without a tax row the host line is untaxed, and the option follows it: the VAT
        // path is only exercised when the line it hangs under carries one.
        (new OrderProductTax())
            ->setOrderProductId($orderProduct->getId())
            ->setTitle('VAT')
            ->setAmount((string) self::HOST_TAX)
            ->setPromoAmount((string) self::HOST_TAX)
            ->save();

        $orderProduct->clearAllReferences();

        return OrderProductQuery::create()->findPk($orderProduct->getId());
    }

    /**
     * @return array{0: CartItem, 1: OptionCartItemService, 2: Product, 3: OptionProduct}
     */
    private function cartLineWithOneOption(): array
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();

        $product = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 100,
            'basePrice' => self::CATALOG_PRICE,
        ]);

        $optionSource = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 100,
            'basePrice' => self::OPTION_PRICE,
        ]);

        $optionProduct = (new OptionProduct())
            ->setProductId($optionSource->getId())
            ->setIsCustomizable(false);
        $optionProduct->save();

        (new ProductAvailableOption())
            ->setProductId($product->getId())
            ->setOptionId($optionProduct->getId())
            ->save();

        $cartItem = $this->addToCart($this->factory->cart(), $product);

        /** @var OptionCartItemService $service */
        $service = $this->getService(OptionCartItemService::class);
        $service->persistCartItemCustomizationData($cartItem, $optionProduct, []);
        $service->handleCartItemOptionPrice($cartItem);

        return [$cartItem, $service, $product, $optionProduct];
    }

    private function addToCart(Cart $cart, Product $product): CartItem
    {
        $event = new CartEvent($cart);
        $event
            ->setProductId($product->getId())
            ->setProductSaleElementsId($this->defaultPseFor($product)->getId())
            ->setQuantity(1)
            ->setNewness(true)
            ->setAppend(true);

        $this->dispatch($event, TheliaEvents::CART_ADDITEM);

        return $event->getCartItem();
    }

    private function changeCurrency(Cart $cart): void
    {
        $request = $this->getService(RequestStack::class)->getMainRequest();
        $request->getSession()->setSessionCart($cart);

        $currency = Currency::getDefaultCurrency();
        $request->getSession()->setCurrency($currency);

        $this->dispatch(new CurrencyChangeEvent($currency, $request), TheliaEvents::CHANGE_DEFAULT_CURRENCY);
    }

    private function reread(CartItem $cartItem): CartItem
    {
        return CartItemQuery::create()->findPk($cartItem->getId())
            ?? self::fail('The cart line is gone.');
    }

    private function defaultPseFor(Product $product): ProductSaleElements
    {
        return ProductSaleElementsQuery::create()
            ->filterByProductId($product->getId())
            ->filterByIsDefault(true)
            ->findOne();
    }
}
