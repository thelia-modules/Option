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

use Option\Event\OptionInputValidationEvent;
use Option\Model\OptionProduct;
use Option\Model\OptionProductQuery;
use Option\Service\Front\OptionCartItemService;
use Option\Service\Front\SelectedOptionsStore;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\CartItem;

class CartItemCustomizationOptionHandler
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected OptionCartItemService $optionCartItemService,
        protected SelectedOptionsStore $selectedOptions,
    ) {
    }

    /**
     * Attaches to a cart line the options the visitor picked on the product page.
     *
     * The selection comes from the add-to-cart form, submitted in this very request. An
     * option the product does not carry is refused by the query, not by a guard: even a
     * hand-forged payload cannot attach an option the merchant never offered.
     *
     * @throws PropelException
     */
    public function updateCustomizationOptionOnCartItem(CartItem $cartItem): void
    {
        $selected = $this->selectedOptions->forProduct($cartItem->getProductId());

        if ([] === $selected) {
            return;
        }

        $optionProducts = OptionProductQuery::create()
            ->filterById(array_keys($selected), Criteria::IN)
            ->useProductAvailableOptionQuery()
                ->filterByProductId($cartItem->getProductId())
            ->endUse()
            ->find();

        $attached = [];

        /** @var OptionProduct $optionProduct */
        foreach ($optionProducts as $optionProduct) {
            $value = $selected[(int) $optionProduct->getId()] ?? null;

            // A ticked box carries no data of its own, only the fact that it was ticked.
            // A customizable option carries the text the visitor typed, which is what
            // ends up in option_cart_item_order_product.customization_data.
            $customization = \is_string($value) ? ['value' => $value] : [];

            // Dispatched either way, so a module extending an option with its own data
            // keeps its hook, and can add to what the visitor typed.
            $extendEvent = (new OptionInputValidationEvent())
                ->setOptionId((int) $optionProduct->getId())
                ->setOptionCustomizationFormData($customization)
                ->setCartItem($cartItem);

            $this->dispatcher->dispatch($extendEvent, OptionInputValidationEvent::CUSTOMIZATION_OPTION_INPUT_EXTEND);

            $this->optionCartItemService->persistCartItemCustomizationData(
                $cartItem,
                $optionProduct,
                $extendEvent->getOptionCustomizationFormData()
            );

            $attached[] = $optionProduct;
        }

        if ([] === $attached) {
            return;
        }

        $this->optionCartItemService->handleCartItemOptionPrice($cartItem, $attached);
    }
}
