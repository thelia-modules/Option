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

namespace Option\Service\Back;

use Option\Model\CategoryAvailableOptionQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Lang;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;

/**
 * Rows of the "Options" tab of the back-office.
 *
 * The tab is rendered inline by the hooks and still answered by the show routes:
 * both read the list here, so the two paths cannot drift apart.
 */
final readonly class OptionTabContextService
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return list<array{optionProductId: int, productId: int, ref: string, title: ?string, price: ?float}>
     */
    public function attachedToProduct(int $productId): array
    {
        return $this->rows(
            ProductAvailableOptionQuery::create()->filterByProductId($productId)->find()
        );
    }

    /**
     * @return list<array{optionProductId: int, productId: int, ref: string, title: ?string, price: ?float}>
     */
    public function attachedToCategory(int $categoryId): array
    {
        return $this->rows(
            CategoryAvailableOptionQuery::create()->filterByCategoryId($categoryId)->find()
        );
    }

    /**
     * @param iterable<object> $availableOptions rows carrying an OptionProduct relation
     *
     * @return list<array{optionProductId: int, productId: int, ref: string, title: ?string, price: ?float}>
     */
    private function rows(iterable $availableOptions): array
    {
        $locale = $this->currentLocale();
        $rows = [];

        foreach ($availableOptions as $availableOption) {
            $optionProduct = $availableOption->getOptionProduct();
            $product = $optionProduct instanceof OptionProduct ? $optionProduct->getProduct() : null;

            // An option whose product is gone is a row nothing can name: skip it
            // rather than render a line with an empty title and a dead link.
            if (!$product instanceof Product) {
                continue;
            }

            $product->setLocale($locale);

            $rows[] = [
                'optionProductId' => $availableOption->getOptionId(),
                'productId' => $product->getId(),
                'ref' => $product->getRef(),
                'title' => $product->getTitle(),
                'price' => $this->price($product),
            ];
        }

        return $rows;
    }

    private function price(Product $product): ?float
    {
        $productPrice = ProductPriceQuery::create()
            ->filterByProductSaleElements($product->getDefaultSaleElements())
            ->orderByCurrencyId(Criteria::ASC)
            ->findOne();

        return null !== $productPrice ? (float) $productPrice->getPrice() : null;
    }

    private function currentLocale(): string
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();

        if ($session instanceof Session) {
            return $session->getAdminEditionLang()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }
}
