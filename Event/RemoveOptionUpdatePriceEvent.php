<?php

declare(strict_types=1);

namespace Option\Event;

use Option\Model\OptionProduct;
use Thelia\Core\Event\ActionEvent;
use Thelia\Model\CartItem;

class RemoveOptionUpdatePriceEvent extends ActionEvent
{

    public const REMOVE_OPTION_UPDATE_PRICE = 'remove_option_update_price';

    /** @var CartItem */
    protected CartItem $cartItem;

    protected array $totalCustoms;

    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    public function setCartItem(CartItem $cartItem): self
    {
        $this->cartItem = $cartItem;
        return $this;
    }

    public function getTotalCustoms(): array
    {
        return $this->totalCustoms;
    }

    public function setTotalCustoms(array $totalCustoms): self
    {
        $this->totalCustoms = $totalCustoms;
        return $this;
    }
}