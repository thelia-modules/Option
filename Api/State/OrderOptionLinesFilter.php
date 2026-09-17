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

use Option\Model\OptionCartItemOrderProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Api\Bridge\Propel\Event\ModelToResourceEvent;
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
 * Hooked on the transform rather than on a provider. A provider decorator only covers the
 * one provider it names, and the order resource has more than one way in: guest order
 * tracking (GET /front/guest-orders/{token}) serves the front groups from
 * GuestOrderProvider, which calls modelToResource() straight and would sail past a
 * decorator. Every provider, present and future, goes through this event.
 *
 * Deliberately front-only: the back-office and the accounting need the lines exactly as
 * they were written.
 */
final readonly class OrderOptionLinesFilter implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ModelToResourceEvent::AFTER_TRANSFORM => [
                ['removeOptionLines', -10],
            ],
        ];
    }

    public function removeOptionLines(ModelToResourceEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource instanceof Order || [] === $resource->getOrderProducts()) {
            return;
        }

        if (!$this->isFrontRead($event->getContext())) {
            return;
        }

        $lineIds = [];

        foreach ($resource->getOrderProducts() as $orderProduct) {
            $id = $this->lineId($orderProduct);

            if (null !== $id) {
                $lineIds[] = $id;
            }
        }

        if ([] === $lineIds) {
            return;
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
            return;
        }

        $resource->setOrderProducts(array_values(array_filter(
            $resource->getOrderProducts(),
            fn ($orderProduct): bool => !\in_array($this->lineId($orderProduct), $optionLineIds, true)
        )));
    }

    /**
     * A front read is one no admin group takes part in — the same rule the core listeners
     * on this event apply, so an admin read embedding an order keeps every line.
     *
     * @param array<string, mixed> $context
     */
    private function isFrontRead(array $context): bool
    {
        $groups = $context['groups'] ?? [];

        if (!\is_array($groups)) {
            $groups = [$groups];
        }

        $isFrontRead = false;

        foreach ($groups as $group) {
            if (!\is_string($group)) {
                continue;
            }

            if (str_starts_with($group, 'admin:')) {
                return false;
            }

            $isFrontRead = $isFrontRead || str_starts_with($group, 'front:');
        }

        return $isFrontRead;
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
