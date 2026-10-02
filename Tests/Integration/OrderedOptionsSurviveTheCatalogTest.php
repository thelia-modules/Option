<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\AttachedOptionsService;
use Option\Service\Front\OptionOrderProductService;
use Thelia\Model\OrderProduct;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * What a customer bought stays on their order: the options listed under a placed order
 * line are read from what the order froze, not from the catalogue the merchant edits.
 */
final class OrderedOptionsSurviveTheCatalogTest extends ActionIntegrationTestCase
{
    public function testTheOptionStaysListedOnceTheMerchantDeletesIt(): void
    {
        [$orderProduct, $option] = $this->placedLineWithAnEngraving();

        $before = $this->getService(AttachedOptionsService::class)->forHostOrderProduct($orderProduct->getId());
        self::assertCount(1, $before);
        self::assertSame('Engraving', $before[0]['title']);
        self::assertSame('Bob', $before[0]['value']);

        $option->delete();

        $after = $this->getService(AttachedOptionsService::class)->forHostOrderProduct($orderProduct->getId());
        self::assertCount(1, $after, 'the option bought is still listed');
        self::assertSame('Engraving', $after[0]['title']);
        self::assertSame('Bob', $after[0]['value']);
        self::assertSame($before[0]['price'], $after[0]['price']);
    }

    public function testTheOptionKeepsTheNameItWasOrderedUnder(): void
    {
        [$orderProduct, , $optionSource] = $this->placedLineWithAnEngraving();

        $optionSource->setLocale('en_US')->setTitle('Laser engraving')->save();

        $options = $this->getService(AttachedOptionsService::class)->forHostOrderProduct($orderProduct->getId());
        self::assertSame('Engraving', $options[0]['title']);
    }

    /**
     * @return array{OrderProduct, OptionProduct, \Thelia\Model\Product}
     */
    private function placedLineWithAnEngraving(): array
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 100.0]);
        $optionSource = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 10,
            'basePrice' => 5.0,
            'title' => 'Engraving',
            'locale' => 'en_US',
        ]);

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(true);
        $option->save();
        $available = (new ProductAvailableOption())->setProductId($product->getId())->setOptionId($option->getId());
        $available->save();

        $order = $this->factory->order();
        $orderProduct = (new OrderProduct())
            ->setOrderId($order->getId())
            ->setProductRef($product->getRef())
            ->setProductSaleElementsRef('X')
            ->setTitle($product->getRef())
            ->setQuantity(1)
            ->setPrice('105')
            ->setPromoPrice('105')
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setVirtual(0);
        $orderProduct->save();

        (new OptionCartItemOrderProduct())
            ->setOrderProductId($orderProduct->getId())
            ->setProductAvailableOptionId($available->getId())
            ->setCustomizationData(json_encode(['value' => 'Bob']))
            ->setPrice('5')
            ->setTaxedPrice('5')
            ->setQuantity('1')
            ->save();

        $this->getService(OptionOrderProductService::class)->handleOrderProduct($orderProduct);

        return [$orderProduct, $option, $optionSource];
    }
}
