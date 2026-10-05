<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\OptionProductService;
use Thelia\Model\CategoryQuery;
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

    public function testRemovingTheOptionFromACategoryKeepsWhatWasAttachedToTheProductItself(): void
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 10.0]);
        $source = $this->factory->product($this->factory->category(), $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 5.0]);
        $option = (new OptionProduct())->setProductId($source->getId())->setIsCustomizable(false);
        $option->save();

        $service = $this->getService(OptionProductService::class);
        // Attached to this product by hand only; the category never carried the option.
        $service->setOptionOnProduct($product->getId(), $option->getId(), OptionProductService::ADDED_BY_PRODUCT);

        $category = CategoryQuery::create()->findPk($category->getId());
        $service->deleteOptionOnCategoryTree($category, $option->getId(), true);

        self::assertSame(
            1,
            ProductAvailableOptionQuery::create()
                ->filterByProductId($product->getId())
                ->filterByOptionId($option->getId())
                ->count(),
            'the product keeps the option the merchant attached to it directly',
        );
    }

    public function testRemovingFromTheProductStillDropsAnOptionOnlyItsCategoryAttached(): void
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 10.0]);
        $source = $this->factory->product($this->factory->category(), $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 5.0]);
        $option = (new OptionProduct())->setProductId($source->getId())->setIsCustomizable(false);
        $option->save();

        $service = $this->getService(OptionProductService::class);
        $service->setOptionOnProduct($product->getId(), $option->getId(), OptionProductService::ADDED_BY_CATEGORY);

        // The back-office delete button on the product page removes it with the product origin.
        $service->deleteOptionOnProduct($option->getId(), $product->getId(), OptionProductService::ADDED_BY_PRODUCT);

        self::assertSame(
            0,
            ProductAvailableOptionQuery::create()
                ->filterByProductId($product->getId())
                ->filterByOptionId($option->getId())
                ->count(),
        );
    }
}
