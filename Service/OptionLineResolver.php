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

namespace Option\Service;

use Option\Model\OptionCartItemOrderProduct;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

/**
 * Follows an option row back to the catalogue product that names it.
 *
 * Every link in that chain can be gone, and none of them says so. option_cart_item_order
 * _product.product_available_option_id is nullable and its foreign key is ON DELETE SET
 * NULL, while product_available_option cascades from both option_product and product: a
 * merchant deleting the product behind an option empties the two tables above and leaves
 * every cart in flight pointing at nothing.
 *
 * Reading that chain without a guard is not a display bug. OptionOrderProductService runs
 * on ORDER_BEFORE_PAYMENT, after the order has been committed and before the payment is
 * called, so an error thrown there leaves an order nobody paid for, a cart nobody emptied
 * and a blank page. An option that can no longer be named drops its line; it never takes
 * the order with it.
 */
final readonly class OptionLineResolver
{
    /**
     * @throws PropelException
     */
    public function resolve(OptionCartItemOrderProduct $optionLine): ?ResolvedOption
    {
        $productAvailableOption = $optionLine->getProductAvailableOption();

        if (null === $productAvailableOption) {
            return null;
        }

        // product_available_option.option_id is required and cascades, so Propel types
        // this getter non-nullable and is right — until a row survives an import or a
        // migration run with the foreign key checks off, which costs nothing to survive.
        /** @phpstan-ignore identical.alwaysFalse */
        if (null === $optionProduct = $productAvailableOption->getOptionProduct()) {
            return null;
        }

        // Read through the query rather than OptionProduct::getProduct(): the generated
        // getter is typed non-nullable while it resolves a foreign key, so a row pointing
        // at a deleted product would slip past a guard the analyser folds away.
        $product = ProductQuery::create()->findPk($optionProduct->getProductId());

        if (!$product instanceof Product) {
            return null;
        }

        return new ResolvedOption($optionProduct, $product);
    }
}
