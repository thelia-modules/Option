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

namespace Option\EventListeners;

use Option\Model\OptionCartItemOrderProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\Front\SelectedOptionsStore;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartItemQuery;

/**
 * Decides which cart line a product added with options lands on.
 *
 * The core looks for an existing line by product and sale element alone
 * (Thelia\Action\Cart::findCartItem), so "Anna" then "Bob" engraved on the same ring
 * would end up as one line of two rings, carrying "Bob" only. A line is reused here only
 * when it carries exactly the options and the values just submitted; when none does, the
 * search stops with no line, and the core opens a new one.
 */
final readonly class CartFindItemListener implements EventSubscriberInterface
{
    public function __construct(
        private SelectedOptionsStore $selectedOptions,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Above Thelia\Action\Cart::findCartItem (128), which would otherwise answer first.
        return [
            TheliaEvents::CART_FINDITEM => ['findLineWithSameOptions', 192],
        ];
    }

    public function findLineWithSameOptions(CartEvent $event): void
    {
        if (null !== $event->getCartItem()) {
            return;
        }

        $productId = (int) $event->getProductId();
        $submitted = $this->offeredSelection($productId);
        ksort($submitted);

        $candidates = CartItemQuery::create()
            ->filterByCartId($event->getCart()->getId())
            ->filterByProductId($productId)
            ->filterByProductSaleElementsId($event->getProductSaleElementsId())
            ->filterByIsOffered(0)
            ->orderById()
            ->find();

        foreach ($candidates as $candidate) {
            // Strict: a loose comparison would take a ticked box (true) for any text.
            if ($this->optionsOnLine((int) $candidate->getId()) === $submitted) {
                $event->setCartItem($candidate);
                $event->stopPropagation();

                return;
            }
        }

        if (!$candidates->isEmpty()) {
            // Lines of this product exist, none with these options: no line is the answer.
            $event->stopPropagation();
        }
    }

    /**
     * The submitted selection, reduced to the options this product actually offers: the
     * others are never attached (CartItemCustomizationOptionHandler), so they cannot tell
     * two lines apart.
     *
     * @return array<int, true|string> value by option id
     */
    private function offeredSelection(int $productId): array
    {
        $selected = $this->selectedOptions->forProduct($productId);

        if ([] === $selected) {
            return [];
        }

        $offered = ProductAvailableOptionQuery::create()
            ->filterByProductId($productId)
            ->filterByOptionId(array_keys($selected), Criteria::IN)
            ->select(['OptionId'])
            ->find()
            ->getData();

        return array_intersect_key($selected, array_flip(array_map('intval', $offered)));
    }

    /**
     * @return array<int, true|string> value by option id, in the shape of the selection
     */
    private function optionsOnLine(int $cartItemId): array
    {
        $options = [];

        $rows = OptionCartItemOrderProductQuery::create()
            ->filterByCartItemOptionId($cartItemId)
            ->joinWithProductAvailableOption()
            ->find();

        foreach ($rows as $row) {
            $data = json_decode((string) $row->getCustomizationData(), true);
            $value = \is_array($data) ? ($data['value'] ?? null) : null;

            $options[(int) $row->getProductAvailableOption()->getOptionId()] = \is_string($value) ? $value : true;
        }

        ksort($options);

        return $options;
    }
}
