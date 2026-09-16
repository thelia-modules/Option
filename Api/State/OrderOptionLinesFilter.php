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

namespace Option\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Option\Model\OptionCartItemOrderProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Thelia\Api\Bridge\Propel\State\PropelItemProvider;
use Thelia\Api\Resource\Order;

/**
 * Takes the option lines out of an order before a front page ever sees them.
 *
 * Placing an order turns every paid option into an order line of its own
 * (OptionOrderProductService::createCustomizationOrderProduct). Those lines are real —
 * they carry the amount, the VAT and the accounting — but they are not products the
 * customer chose to buy, and a theme listing the order shows them as such.
 *
 * Filtering here rather than in the theme is what keeps the theme from having to know
 * this module exists: it renders whatever the API hands it, and what the module has to
 * say about a line arrives through the account-order.item.bottom hook instead.
 *
 * Deliberately front-only: the back-office and the accounting need the lines exactly as
 * they were written.
 */
#[AsDecorator(PropelItemProvider::class)]
final readonly class OrderOptionLinesFilter implements ProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ProviderInterface $inner,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resource = $this->inner->provide($operation, $uriVariables, $context);

        if (!$resource instanceof Order || [] === $resource->getOrderProducts()) {
            return $resource;
        }

        if (!$this->isFrontRead($operation, $context)) {
            return $resource;
        }

        $lineIds = [];

        foreach ($resource->getOrderProducts() as $orderProduct) {
            $id = $this->lineId($orderProduct);

            if (null !== $id) {
                $lineIds[] = $id;
            }
        }

        if ([] === $lineIds) {
            return $resource;
        }

        // One query for the whole order: the lines an option became are exactly those an
        // option row points at through option_order_product_id.
        $optionLineIds = OptionCartItemOrderProductQuery::create()
            ->filterByOptionOrderProductId($lineIds, Criteria::IN)
            ->select(['OptionOrderProductId'])
            ->find()
            ->toArray();

        $optionLineIds = array_map(intval(...), $optionLineIds);

        if ([] === $optionLineIds) {
            return $resource;
        }

        $resource->setOrderProducts(array_values(array_filter(
            $resource->getOrderProducts(),
            fn ($orderProduct): bool => !\in_array($this->lineId($orderProduct), $optionLineIds, true)
        )));

        return $resource;
    }

    /**
     * The back-office reads the same resource and must keep every line, so the filter
     * only applies where the front groups are asked for.
     *
     * @param array<string, mixed> $context
     */
    private function isFrontRead(Operation $operation, array $context): bool
    {
        $groups = $context['groups']
            ?? $operation->getNormalizationContext()['groups']
            ?? [];

        if (!\is_array($groups)) {
            $groups = [$groups];
        }

        foreach ($groups as $group) {
            if (\is_string($group) && str_starts_with($group, 'front:')) {
                return true;
            }
        }

        return false;
    }

    private function lineId(mixed $orderProduct): ?int
    {
        if (\is_object($orderProduct) && method_exists($orderProduct, 'getId')) {
            return (int) $orderProduct->getId();
        }

        if (\is_array($orderProduct) && isset($orderProduct['id'])) {
            return (int) $orderProduct['id'];
        }

        return null;
    }
}
