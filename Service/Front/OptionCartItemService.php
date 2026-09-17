<?php

declare(strict_types=1);

namespace Option\Service\Front;

use Option\Event\OptionUpdatePriceEvent;
use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\OptionService;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Domain\Taxation\TaxEngine\Calculator;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\CartItem;

class OptionCartItemService
{
    /**
     * Amounts map DECIMAL(16,6) columns: below that, two prices are the same price.
     */
    private const PRICE_EPSILON = 0.0000005;

    protected ?Request $request;

    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected OptionService $optionService,
        protected TaxEngine $taxEngine,
        RequestStack $request
    )
    {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * Brings a cart line's price back in line with the options attached to it.
     *
     * option_cart_item_order_product is the only source of truth for what an option
     * costs; cart_item.price merely carries the sum, because the cart total is built
     * from that column alone (Thelia\Model\Cart::getTaxedAmount) and no extension point
     * lets a module add to it — which is also what the postage thresholds, the coupons
     * and the cart API resource read.
     *
     * The core resets the line to the catalog price whenever it refreshes the cart
     * (Thelia\Action\Cart::refreshCartItemPrices, reached from a currency change, a cart
     * restored from its cookie, and every add or quantity change while a reserved
     * operation runs), and says nothing about it. So the line price is computed here in
     * the absolute — catalog price plus the options on record — never incremented: a
     * line already at that figure is left untouched, which makes the pass idempotent
     * whatever calls it, and however many times.
     *
     * The promo price moves by the same delta rather than being recomputed, so a
     * reserved price the core settled on this line survives the repair.
     *
     * @throws PropelException
     */
    public function reconcileCartItemPrice(CartItem $cartItem): void
    {
        $catalogPrice = $this->catalogPrice($cartItem);

        if (null === $catalogPrice) {
            return;
        }

        $target = round($catalogPrice + $this->optionSupplement($cartItem), 6);
        $delta = round($target - (float) $cartItem->getPrice(), 6);

        if (abs($delta) < self::PRICE_EPSILON) {
            return;
        }

        $cartItem
            ->setPrice((string) $target)
            ->setPromoPrice((string) round((float) $cartItem->getPromoPrice() + $delta, 6))
            ->save();
    }

    /**
     * @throws PropelException
     */
    public function handleCartItemOptionPrice(CartItem $cartItem): void
    {
        $supplement = $this->optionSupplement($cartItem);

        $event = new OptionUpdatePriceEvent();
        $event
            ->setCartItem($cartItem)
            ->setTotalCustoms([
                'totalCustomizationPrice' => $supplement,
                'totalCustomizationPromoPrice' => $supplement,
            ]);

        $this->dispatcher->dispatch($event, OptionUpdatePriceEvent::OPTION_UPDATE_PRICE);

        $this->reconcileCartItemPrice($cartItem);
    }

    /**
     * What the options attached to this line add to its untaxed unit price.
     *
     * Read off the rows, not recomputed from the option catalog: the amount was frozen
     * when the visitor picked the option, and that is the amount the order lines are
     * built from in OptionOrderProductService.
     *
     * @throws PropelException
     */
    public function optionSupplement(CartItem $cartItem): float
    {
        $total = 0.0;

        /** @var OptionCartItemOrderProduct $optionLine */
        foreach (OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($cartItem->getId())->find() as $optionLine) {
            $total += (float) $optionLine->getPrice();
        }

        return $total;
    }

    /**
     * The untaxed price the core would give this line if it refreshed it right now.
     *
     * Same call as Thelia\Action\Cart::refreshCartItemPrices, so the two agree on what
     * "catalog price" means, customer discount included.
     *
     * @throws PropelException
     */
    private function catalogPrice(CartItem $cartItem): ?float
    {
        // Propel's generated PHPDoc types these relations as never null, and the getters
        // hand back null all the same when there is no id to follow — which is why the
        // core guards the very same calls in Thelia\Action\Cart::refreshCartItemPrices().
        /** @phpstan-ignore identical.alwaysFalse */
        if (null === $productSaleElements = $cartItem->getProductSaleElements()) {
            return null;
        }

        $cart = $cartItem->getCart();

        if (null === $currency = $cart->getCurrency()) {
            return null;
        }

        $customer = $cart->getCustomer();
        // getDiscount() maps a DECIMAL column and returns a string.
        $discount = null !== $customer && $customer->getDiscount() > 0 ? (float) $customer->getDiscount() : 0.0;

        try {
            return (float) $productSaleElements->getPricesByCurrency($currency, $discount)->getPrice();
        } catch (\RuntimeException) {
            // No price for this currency: the core cannot price the line either, and a
            // line we cannot anchor on a catalog price is not one to rewrite.
            return null;
        }
    }

    /**
     * @throws PropelException
     */
    public function persistCartItemCustomizationData(CartItem $cartItem, OptionProduct $optionProduct, array $formData): void
    {
        $productAvailableOption = ProductAvailableOptionQuery::create()
            ->filterByProductId($cartItem->getProductId())
            ->filterByOptionId($optionProduct->getId())
            ->findOne();

        if (null === $productAvailableOption) {
            return;
        }

        $optionCartItem = OptionCartItemOrderProductQuery::create()
            ->filterByProductAvailableOptionId($productAvailableOption->getId())
            ->filterByCartItemOptionId($cartItem->getId())->findOne();

        if (null === $optionCartItem) {
            $optionCartItem = new OptionCartItemOrderProduct();
        }

        $optionCartItem
            ->setCartItemOptionId($cartItem->getId())
            ->setProductAvailableOptionId($productAvailableOption->getId());

        $fields = ['optionId', 'optionCode', 'error_message', 'success_url', 'error_url'];

        $customization = array_filter($formData, function ($key) use ($fields) {
            return !in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);

        $taxCalculator = $this->getTaxCalculator($cartItem);
        $price = $this->optionService->getOptionTaxedPrice($optionProduct->getProduct());
        $untaxedPrice = $taxCalculator->getUntaxedPrice($price);

        $optionCartItem
            ->setPrice((string) $untaxedPrice)
            ->setTaxedPrice((string) $price)
            ->setCustomizationData(json_encode($customization))
            ->setQuantity((string) $cartItem->getQuantity())
            ->save();
    }

    /**
     * @param CartItem $cartItem
     * @return Calculator
     * @throws PropelException
     */
    private function getTaxCalculator(CartItem $cartItem): Calculator
    {
        $taxCalculator = new Calculator();
        $taxCalculator->load($cartItem->getProduct(), $this->taxEngine->getDeliveryCountry(), $this->taxEngine->getDeliveryState());
        return $taxCalculator;
    }
}
