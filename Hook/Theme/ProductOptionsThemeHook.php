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
use Option\Service\Front\ProductOptionsService;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Thelia\Core\Translation\Translator;
use Twig\Environment;

/**
 * Paid options of the product, under its details block on the product page.
 *
 * The theme declares the point with theme_hook('product.details.bottom', {product: product}),
 * where `product` is the front API resource the page has already loaded.
 */
final readonly class ProductOptionsThemeHook implements ThemeHookInterface
{
    private const HOOK_NAME = 'product.details.bottom';

    public function __construct(
        private Environment $twig,
        private ProductOptionsService $productOptions,
        private Translator $translator,
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
        $options = $this->productOptions->forProduct($this->productId($parameters));

        // A product without options gets no block at all, not an empty one.
        if ([] === $options) {
            return '';
        }

        return $this->twig->render('@OptionModule/theme_hook/options.html.twig', [
            'options' => $options,
            // The front Twig `trans` filter is Symfony's, fed by the theme's translations/
            // directory: it cannot see a module domain. Labels are resolved here instead.
            'labels' => [
                'title' => $this->translator->trans('Available options', [], Option::DOMAIN_NAME),
            ],
        ]);
    }

    /**
     * The theme passes the product as the front API resource, decoded to an array.
     * A theme that hands over the Propel model, or the id alone, is read too.
     *
     * @param array<string, mixed> $parameters
     */
    private function productId(array $parameters): int
    {
        $product = $parameters['product'] ?? null;

        if (\is_array($product)) {
            return (int) ($product['id'] ?? 0);
        }

        if (\is_object($product) && method_exists($product, 'getId')) {
            return (int) $product->getId();
        }

        return (int) ($parameters['product_id'] ?? 0);
    }
}
