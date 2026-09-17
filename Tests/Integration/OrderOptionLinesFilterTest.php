<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Model\OptionCartItemOrderProduct;
use Thelia\Api\Bridge\Propel\Service\ApiResourcePropelTransformerService;
use Thelia\Api\Resource\Order as OrderResource;
use Thelia\Model\Order;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderQuery;
use Thelia\Test\ActionIntegrationTestCase;

/**
 * The lines an option became are taken out of every front read of an order.
 *
 * Read through the transformer rather than through one provider: guest order tracking
 * serves the front groups from GuestOrderProvider, which calls the transformer straight,
 * and that is exactly the way in a provider decorator cannot see.
 */
final class OrderOptionLinesFilterTest extends ActionIntegrationTestCase
{
    public function testAFrontReadDropsTheLineAnOptionBecame(): void
    {
        [$order, $optionLineId] = $this->orderWithOneOptionLine();

        $lineIds = $this->orderProductIdsOf($order, [
            OrderResource::GROUP_FRONT_READ,
            OrderResource::GROUP_FRONT_READ_SINGLE,
        ]);

        self::assertNotContains($optionLineId, $lineIds, 'the option line never reaches a front page');
        self::assertNotSame([], $lineIds, 'the product the customer bought is still there');
    }

    public function testAnAdminReadKeepsEveryLine(): void
    {
        [$order, $optionLineId] = $this->orderWithOneOptionLine();

        $lineIds = $this->orderProductIdsOf($order, [
            OrderResource::GROUP_ADMIN_READ,
            OrderResource::GROUP_ADMIN_READ_SINGLE,
        ]);

        self::assertContains($optionLineId, $lineIds, 'the back office and the accounting keep the line');
    }

    /**
     * @param list<string> $groups
     *
     * @return list<int>
     */
    private function orderProductIdsOf(Order $order, array $groups): array
    {
        /** @var ApiResourcePropelTransformerService $transformer */
        $transformer = $this->getService(ApiResourcePropelTransformerService::class);

        $order->clearAllReferences();

        /** @var OrderResource $resource */
        $resource = $transformer->modelToResource(
            OrderResource::class,
            OrderQuery::create()->findPk($order->getId()),
            ['groups' => $groups],
        );

        return array_map(
            static fn ($orderProduct): int => (int) $orderProduct->getId(),
            $resource->getOrderProducts(),
        );
    }

    /**
     * @return array{0: Order, 1: int}
     */
    private function orderWithOneOptionLine(): array
    {
        $order = $this->factory->order();

        $hostLine = $this->orderProduct($order, 'HOST-REF', 'A product');
        $optionLine = $this->orderProduct($order, 'Personalisation', 'An option');

        (new OptionCartItemOrderProduct())
            ->setOrderProductId($hostLine->getId())
            ->setOptionOrderProductId($optionLine->getId())
            ->setPrice('10')
            ->setTaxedPrice('11')
            ->setQuantity('1')
            ->save();

        return [$order, (int) $optionLine->getId()];
    }

    private function orderProduct(Order $order, string $ref, string $title): OrderProduct
    {
        $orderProduct = (new OrderProduct())
            ->setOrderId($order->getId())
            ->setProductRef($ref)
            ->setProductSaleElementsRef($ref)
            ->setTitle($title)
            ->setQuantity(1)
            ->setPrice('100')
            ->setPromoPrice('100')
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setVirtual(0);
        $orderProduct->save();

        return $orderProduct;
    }
}
