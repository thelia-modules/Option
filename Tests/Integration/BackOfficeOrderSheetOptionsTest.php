<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Service\Front\OptionOrderProductService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\OrderProduct;
use Thelia\Test\FixtureFactory;
use Thelia\Test\WebIntegrationTestCase;
use Thelia\Tests\Support\BackOffice\AdminSessionInjector;

/**
 * The order sheet of the back office lists, under the line they were bought with, the
 * options of the order by name and with what the customer typed — never as raw JSON.
 */
final class BackOfficeOrderSheetOptionsTest extends WebIntegrationTestCase
{
    private AdminSessionInjector $injector;

    private FixtureFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injector = new AdminSessionInjector();
        $this->getService(EventDispatcherInterface::class)->addSubscriber($this->injector);
        $this->factory = new FixtureFactory($this->getPropelConnection());
    }

    protected function tearDown(): void
    {
        if (isset($this->injector)) {
            $this->injector->clear();
        }
        parent::tearDown();
    }

    public function testTheOrderSheetNamesTheOptionAndShowsWhatTheCustomerTyped(): void
    {
        $orderId = $this->orderWithAnEngraving('Engraving', 'MAMAON');

        $admin = $this->factory->admin();
        $admin->eraseCredentials();
        $this->injector->setAdmin($admin);

        $crawler = $this->client->request('GET', '/admin/order/update/'.$orderId);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $block = $crawler->filter('[data-testid="order-item-options"]');
        self::assertCount(1, $block, 'one block, under the host line only');
        self::assertStringContainsString('Engraving', $block->text());
        self::assertStringContainsString('MAMAON', $block->text());
        self::assertStringNotContainsString('"value"', (string) $this->client->getResponse()->getContent());
    }

    private function orderWithAnEngraving(string $optionTitle, string $text): int
    {
        $currency = $this->factory->currency();
        $taxRule = $this->factory->taxRule();
        $category = $this->factory->category();
        $product = $this->factory->product($category, $taxRule, $currency, ['baseQuantity' => 10, 'basePrice' => 100.0]);
        $optionSource = $this->factory->product($category, $taxRule, $currency, [
            'baseQuantity' => 10,
            'basePrice' => 5.0,
            'title' => $optionTitle,
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
            ->setCustomizationData(json_encode(['value' => $text]))
            ->setPrice('5')
            ->setTaxedPrice('5')
            ->setQuantity('1')
            ->save();

        $this->getService(OptionOrderProductService::class)->handleOrderProduct($orderProduct);

        return (int) $order->getId();
    }
}
