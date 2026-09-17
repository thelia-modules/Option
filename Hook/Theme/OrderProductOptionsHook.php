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

namespace Option\Hook\Theme;

use Option\Option;
use Option\Service\Front\AttachedOptionsService;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Thelia\Core\Translation\Translator;
use Twig\Environment;

/**
 * What the module has to say about an order line, under it.
 *
 * Placing an order splits an option off its product: OptionOrderProductService gives each
 * one its own order line and subtracts its amount from the host. OrderOptionLinesFilter
 * then keeps those lines out of what a front page reads, so the only line reaching this
 * hook is the one options were bought under, and what it gets is the list of them — with
 * the text the customer typed, which the order records nowhere else.
 *
 * Deliberately not called "included": the price shown on that card no longer contains
 * them, the option lines do.
 *
 * The theme declares the point with
 * theme_hook('account-order.item.bottom', {order: order, orderProduct: line.orderProduct}).
 */
final readonly class OrderProductOptionsHook implements ThemeHookInterface
{
    private const HOOK_NAME = 'account-order.item.bottom';

    public function __construct(
        private Environment $twig,
        private AttachedOptionsService $attachedOptions,
    ) {
    }

    public function supports(string $hookName): bool
    {
        return self::HOOK_NAME === $hookName;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(string $hookName, array $parameters): string
    {
        $orderProductId = $this->orderProductId($parameters);

        if ($orderProductId <= 0) {
            return '';
        }

        $options = $this->attachedOptions->forHostOrderProduct($orderProductId);

        // An ordinary line gets nothing at all, not an empty block: the list is already
        // dense, and an empty container would still take its margin.
        if ([] === $options) {
            return '';
        }

        return $this->twig->render('@OptionModule/theme_hook/order_product_options.html.twig', [
            'options' => $options,
            'i18ns' => [
                'options_ordered' => Translator::getInstance()->trans('Options ordered with this product:', [], Option::DOMAIN_NAME),
            ],
        ]);
    }

    /**
     * The theme passes its own order line payload, an array in the account order pages.
     * A theme handing over the Propel model, or the id alone, is read too.
     *
     * @param array<string, mixed> $parameters
     */
    private function orderProductId(array $parameters): int
    {
        $orderProduct = $parameters['orderProduct'] ?? null;

        if (\is_array($orderProduct)) {
            return (int) ($orderProduct['id'] ?? 0);
        }

        if (\is_object($orderProduct)) {
            if (method_exists($orderProduct, 'getId')) {
                return (int) $orderProduct->getId();
            }

            if (property_exists($orderProduct, 'id')) {
                return (int) $orderProduct->id;
            }
        }

        return (int) ($parameters['order_product_id'] ?? 0);
    }
}
