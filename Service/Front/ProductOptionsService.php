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

use Option\Model\OptionProduct;
use Option\Service\OptionService;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Lang;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

/**
 * Rows of the paid options a front page shows for a product.
 *
 * The list comes from OptionService::getProductAvailableOptions(), which dispatches
 * CheckOptionEvent: a module that invalidates an option for this visitor is honoured
 * here the same way it is in the cart.
 */
final readonly class ProductOptionsService
{
    public function __construct(
        private OptionService $optionService,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return list<array{id: int, ref: string, title: ?string, chapo: ?string, price: float, promoPrice: float, promo: bool, customizable: bool}>
     */
    public function forProduct(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }

        $product = ProductQuery::create()->findPk($productId);

        if (!$product instanceof Product) {
            return [];
        }

        $locale = $this->currentLocale();
        $rows = [];

        foreach ($this->optionService->getProductAvailableOptions($product) ?? [] as $optionProduct) {
            $row = $this->row($optionProduct, $locale);

            if (null !== $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array{id: int, ref: string, title: ?string, chapo: ?string, price: float, promoPrice: float, promo: bool, customizable: bool}|null
     */
    private function row(mixed $optionProduct, string $locale): ?array
    {
        if (!$optionProduct instanceof OptionProduct) {
            return null;
        }

        // Read through the query rather than OptionProduct::getProduct(): the generated
        // getter is typed non-nullable while it resolves a foreign key, so a row pointing
        // at a deleted product would slip past a guard the analyser folds away.
        $option = ProductQuery::create()->findPk($optionProduct->getProductId());

        // An option whose product is gone, or that the merchant unpublished, has no
        // business on a shop page: skip the row rather than render a nameless line.
        if (!$option instanceof Product || !$option->getVisible()) {
            return null;
        }

        // Product::getDefaultSaleElements() declares a non-nullable return type on a
        // findOne(): an option without a default sale element makes the core throw
        // inside its own getter, where no caller can guard. The sale element is read
        // here instead, and a missing one drops the row.
        $defaultPse = ProductSaleElementsQuery::create()
            ->filterByProductId($option->getId())
            ->filterByIsDefault(true)
            ->findOne();

        if (!$defaultPse instanceof ProductSaleElements) {
            return null;
        }

        $option->setLocale($locale);

        return [
            'id' => (int) $optionProduct->getId(),
            'ref' => (string) $option->getRef(),
            'title' => $option->getTitle(),
            'chapo' => $option->getChapo(),
            'price' => (float) $this->optionService->getOptionTaxedPrice($option),
            'promoPrice' => (float) $this->optionService->getOptionTaxedPrice($option, true),
            'promo' => (bool) $defaultPse->getPromo(),
            'customizable' => (bool) $optionProduct->getIsCustomizable(),
        ];
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
