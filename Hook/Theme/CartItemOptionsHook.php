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

use Option\Service\Front\AttachedOptionsService;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Twig\Environment;

/**
 * The paid options of a cart line, listed under it in the checkout tunnel.
 *
 * The theme declares the point with theme_hook('cart.item.bottom', {cartItem: cartItem}),
 * where `cartItem` is the DTO the card already holds. Nothing is computed theme-side:
 * the id is all the module needs to find what is attached to the line.
 */
final readonly class CartItemOptionsHook implements ThemeHookInterface
{
    private const HOOK_NAME = 'cart.item.bottom';

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
        $options = $this->attachedOptions->forCartItem($this->cartItemId($parameters));

        // A line without options gets nothing at all, not an empty block: the card is
        // already dense, and an empty container would still take its margin.
        if ([] === $options) {
            return '';
        }

        return $this->twig->render('@OptionModule/theme_hook/cart_item_options.html.twig', [
            'options' => $options,
        ]);
    }

    /**
     * The theme passes its own cart item DTO. A theme handing over the Propel model, an
     * array, or the id alone is read too.
     *
     * @param array<string, mixed> $parameters
     */
    private function cartItemId(array $parameters): int
    {
        $cartItem = $parameters['cartItem'] ?? null;

        if (\is_array($cartItem)) {
            return (int) ($cartItem['id'] ?? 0);
        }

        if (\is_object($cartItem)) {
            if (method_exists($cartItem, 'getId')) {
                return (int) $cartItem->getId();
            }

            if (property_exists($cartItem, 'id')) {
                return (int) $cartItem->id;
            }
        }

        return (int) ($parameters['cart_item_id'] ?? 0);
    }
}
