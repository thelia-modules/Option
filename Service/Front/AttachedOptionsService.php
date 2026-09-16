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
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Lang;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

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
 */
final readonly class AttachedOptionsService
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    /**
     * Options attached to a cart line.
     *
     * @return list<array{id: int, title: string, value: ?string, price: float}>
     */
    public function forCartItem(int $cartItemId): array
    {
        if ($cartItemId <= 0) {
            return [];
        }

        return $this->rows(
            OptionCartItemOrderProductQuery::create()->filterByCartItemOptionId($cartItemId)->find()
        );
    }

    /**
     * Options attached to an order line, read from the line they were bought under.
     *
     * @return list<array{id: int, title: string, value: ?string, price: float}>
     */
    public function forHostOrderProduct(int $orderProductId): array
    {
        if ($orderProductId <= 0) {
            return [];
        }

        return $this->rows(
            OptionCartItemOrderProductQuery::create()->filterByOrderProductId($orderProductId)->find()
        );
    }

    /**
     * The text the customer typed, read from the order line the option became.
     *
     * Answers null for any other line, which is what tells an option line apart from an
     * ordinary one: the front has nothing else to go on, the order carries no flag.
     */
    public function customizationForOptionOrderProduct(int $orderProductId): ?string
    {
        if ($orderProductId <= 0) {
            return null;
        }

        $optionLine = OptionCartItemOrderProductQuery::create()
            ->filterByOptionOrderProductId($orderProductId)
            ->findOne();

        return null === $optionLine ? null : $this->customizationValue($optionLine);
    }

    /**
     * @param iterable<OptionCartItemOrderProduct> $attached
     *
     * @return list<array{id: int, title: string, value: ?string, price: float}>
     */
    private function rows(iterable $attached): array
    {
        $locale = $this->currentLocale();
        $rows = [];

        foreach ($attached as $optionLine) {
            $row = $this->row($optionLine, $locale);

            if (null !== $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array{id: int, title: string, value: ?string, price: float}|null
     */
    private function row(OptionCartItemOrderProduct $optionLine, string $locale): ?array
    {
        // The row keeps its foreign key nullable and ON DELETE SET NULL: an option the
        // merchant removed from the catalogue leaves a line behind with nothing to name
        // it, and a nameless line under a product helps nobody.
        $productAvailableOption = $optionLine->getProductAvailableOption();

        if (null === $productAvailableOption) {
            return null;
        }

        $optionProduct = $productAvailableOption->getOptionProduct();

        if (null === $optionProduct) {
            return null;
        }

        // Read through the query rather than OptionProduct::getProduct(): the generated
        // getter is typed non-nullable while it resolves a foreign key, so a row pointing
        // at a deleted product would slip past a guard the analyser folds away.
        $product = ProductQuery::create()->findPk($optionProduct->getProductId());

        if (!$product instanceof Product) {
            return null;
        }

        $product->setLocale($locale);

        return [
            'id' => (int) $optionProduct->getId(),
            'title' => (string) ($product->getTitle() ?: $product->getRef()),
            'value' => $this->customizationValue($optionLine),
            // A DECIMAL column comes back as a string.
            'price' => (float) $optionLine->getTaxedPrice(),
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
