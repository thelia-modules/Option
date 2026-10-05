<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\OptionProductService;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * Removing an option through one origin (product, category, template) only takes back
 * what that origin attached.
 */
final class OptionOriginRemovalTest extends ActionIntegrationTestCase
{
    public function testRemovingAnOriginThatWasNeverRecordedLeavesTheOtherOriginsAlone(): void
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 10.0]);
        $source = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 5.0]);
        $option = (new OptionProduct())->setProductId($source->getId())->setIsCustomizable(false);
        $option->save();

        $service = $this->getService(OptionProductService::class);
        $service->setOptionOnProduct($product->getId(), $option->getId(), OptionProductService::ADDED_BY_PRODUCT);
        $service->setOptionOnProduct($product->getId(), $option->getId(), OptionProductService::ADDED_BY_CATEGORY);

        // The template never attached this option to the product: removing it is a no-op.
        $service->deleteOptionOnProduct($option->getId(), $product->getId(), OptionProductService::ADDED_BY_TEMPLATE);

        $row = ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId())
            ->filterByOptionId($option->getId())
            ->findOne();

        self::assertNotNull($row, 'the option is still attached');
        $origins = $row->getOptionAddedBy();
        sort($origins);
        self::assertSame([OptionProductService::ADDED_BY_PRODUCT, OptionProductService::ADDED_BY_CATEGORY], $origins);
    }
}
