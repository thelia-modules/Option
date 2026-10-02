<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\OptionOrderProductService;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * The invoiced option line carries the option's name, whatever language the shop runs in.
 */
final class OptionLineTitleLocaleTest extends ActionIntegrationTestCase
{
    public function testTheOptionLineIsNamedWhenTheOptionIsTitledInTheShopLanguageOnly(): void
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 100.0]);
        $optionSource = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 10,
            'basePrice' => 5.0,
            'title' => 'Gift wrap',
            'locale' => 'en_US',
        ]);

        $option = (new OptionProduct())->setProductId($optionSource->getId())->setIsCustomizable(false);
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
            ->setPrice('100')
            ->setPromoPrice('100')
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setVirtual(0);
        $orderProduct->save();

        (new OptionCartItemOrderProduct())
            ->setOrderProductId($orderProduct->getId())
            ->setProductAvailableOptionId($available->getId())
            ->setPrice('5')
            ->setTaxedPrice('5')
            ->setQuantity('1')
            ->save();

        $this->getService(OptionOrderProductService::class)->handleOrderProduct($orderProduct);

        $line = OrderProductQuery::create()
            ->filterByOrderId($order->getId())
            ->filterByProductRef('Personalisation')
            ->findOne();

        self::assertNotNull($line);
        self::assertSame('Gift wrap', $line->getTitle());
    }
}
