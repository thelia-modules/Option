<?php

namespace Option\Event;

use Option\Model\OptionProduct;
use Thelia\Core\Event\ActionEvent;
use Thelia\Model\CartItem;

class OptionUpdatePriceEvent extends ActionEvent
{

    public const OPTION_UPDATE_PRICE = 'option_update_price';

    /** @var CartItem */
    protected CartItem $cartItem;

    protected array $totalCustoms;

    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    public function setCartItem(CartItem $cartItem): OptionUpdatePriceEvent
    {
        $this->cartItem = $cartItem;
        return $this;
    }

    public function getTotalCustoms(): array
    {
        return $this->totalCustoms;
    }

    public function setTotalCustoms(array $totalCustoms): OptionUpdatePriceEvent
    {
        $this->totalCustoms = $totalCustoms;
        return $this;
    }
}