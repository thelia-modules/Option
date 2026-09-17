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

namespace Option\Service\Front;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Option\Service\OptionLineResolver;
use Thelia\Model\CartItemQuery;
use Thelia\Model\OrderProductQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Lang;

/**
 * What option_cart_item_order_product holds about a line the front is about to show.
 *
 * The single place that reads that table for the front office, because its three foreign
 * keys mean three different things and mixing them up is silent:
 *
 *  - cart_item_option_id     the cart line the option is attached to
 *  - order_product_id        the order line the option is attached to
 *  - option_order_product_id the order line the option BECAME, once the order was placed
 *
 * The prices differ in meaning too. In a cart, OptionCartItemService adds every option to
 * cart_item.price, so what this returns is a breakdown of a total already charged. In an
 * order, OptionOrderProductService gives each option its own order line and subtracts it
 * from the host line, so the same figures are a cross-reference, not a breakdown. Callers
 * have to say which, and the templates label them differently.
 *
 * They differ in tax, too, and that is why each caller states which price it wants. A cart
 * card prices a line with CartItem::getTaxedPrice(), an order card prints order_product's
 * own price, which is untaxed. An option quoted on the wrong basis does not merely look
 * odd: it stops adding up to the total shown beside it.
 *
 * What is returned is the amount for the whole line, option price times the quantity of
 * the line it hangs on — an option is bought once per unit, never once per order. The
 * quantity travels with it so the template can say so: the cards beside it print a unit
 * price, and a line amount presented as if it were one would mislead.
 */
final readonly class AttachedOptionsService
{
    public function __construct(
        private RequestStack $requestStack,
        private OptionLineResolver $optionLineResolver,
    ) {
    }

    /**
     * Options attached to a cart line.
     *
     * @return list<array{id: int, title: string, value: ?string, price: float, quantity: int}>
     */
    public function forCartItem(int $cartItemId): array
    {
        if ($cartItemId <= 0) {
            return [];
        }

        // Read from the cart line rather than from the snapshot the option row keeps:
        // that one is written when the option is attached and never again, so it lies as
        // soon as the customer changes the quantity.
        $quantity = (int) (CartItemQuery::create()->findPk($cartItemId)?->getQuantity() ?? 1);

        // Taxed: the cart cards beside these figures are priced with getTaxedPrice().
        return $this->rows(
            OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($cartItemId)->find(),
            taxed: true,
            quantity: $quantity
        );
    }

    /**
     * Options attached to an order line, read from the line they were bought under.
     *
     * @return list<array{id: int, title: string, value: ?string, price: float, quantity: int}>
     */
    public function forHostOrderProduct(int $orderProductId): array
    {
        if ($orderProductId <= 0) {
            return [];
        }

        // An option line is created with the quantity of the line it hangs on, so the
        // host is the one source both agree with.
        $quantity = (int) (OrderProductQuery::create()->findPk($orderProductId)?->getQuantity() ?? 1);

        // Untaxed: an order card prints order_product.price, and the subtotal under the
        // list is the sum of those. Quoting the taxed figure here would show an option
        // the customer could not find in any total on the page.
        return $this->rows(
            OptionCartItemOrderProductQuery::create()->filterByOrderProductId($orderProductId)->find(),
            taxed: false,
            quantity: $quantity
        );
    }

    /**
     * @param iterable<OptionCartItemOrderProduct> $attached
     *
     * @return list<array{id: int, title: string, value: ?string, price: float, quantity: int}>
     */
    private function rows(iterable $attached, bool $taxed, int $quantity): array
    {
        $locale = $this->currentLocale();
        $quantity = max(1, $quantity);
        $rows = [];

        foreach ($attached as $optionLine) {
            $row = $this->row($optionLine, $locale, $taxed, $quantity);

            if (null !== $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array{id: int, title: string, value: ?string, price: float, quantity: int}|null
     */
    private function row(OptionCartItemOrderProduct $optionLine, string $locale, bool $taxed, int $quantity): ?array
    {
        // A nameless line under a product helps nobody: an option the merchant removed
        // from the catalogue leaves a row behind with nothing left to name it.
        $resolved = $this->optionLineResolver->resolve($optionLine);

        if (null === $resolved) {
            return null;
        }

        $product = $resolved->product;
        $product->setLocale($locale);

        return [
            'id' => (int) $resolved->optionProduct->getId(),
            'title' => (string) ($product->getTitle() ?: $product->getRef()),
            'value' => $this->customizationValue($optionLine),
            // A DECIMAL column comes back as a string. Multiplied here rather than in the
            // template: the amount and the quantity it covers must not be able to drift.
            'price' => (float) ($taxed ? $optionLine->getTaxedPrice() : $optionLine->getPrice()) * $quantity,
            'quantity' => $quantity,
        ];
    }

    /**
     * What the visitor typed on a customizable option, as stored by
     * OptionCartItemService::persistCartItemCustomizationData().
     */
    private function customizationValue(OptionCartItemOrderProduct $optionLine): ?string
    {
        $raw = $optionLine->getCustomizationData();

        if (null === $raw || '' === $raw) {
            return null;
        }

        // Whatever a past version of the module wrote here is not worth a 500 on the cart
        // page: an undecodable payload is simply a line without a value.
        try {
            $decoded = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!\is_array($decoded) || !isset($decoded['value']) || !\is_scalar($decoded['value'])) {
            return null;
        }

        $value = trim((string) $decoded['value']);

        return '' === $value ? null : $value;
    }

    private function currentLocale(): string
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();

        if ($session instanceof Session) {
            return $session->getLang()?->getLocale() ?? Lang::getDefaultLanguage()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }
}
