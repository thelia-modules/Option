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

use Option\Model\OptionProduct;
use Thelia\Model\Product;

/**
 * An option row that still has everything it takes to be named and priced.
 */
final readonly class ResolvedOption
{
    public function __construct(
        public OptionProduct $optionProduct,
        public Product $product,
    ) {
    }
}
