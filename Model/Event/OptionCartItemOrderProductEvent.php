<?php

namespace Option\Model\Event;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Event\ActiveRecordEvent;
use Option\Model\OptionCartItemOrderProduct;

class OptionCartItemOrderProductEvent extends ActiveRecordEvent
{
    const PRE_SAVE = 'propel.pre.save.option_cart_item_order_product';
    const POST_SAVE = 'propel.post.save.option_cart_item_order_product';
    const PRE_INSERT = 'propel.pre.insert.option_cart_item_order_product';
    const POST_INSERT = 'propel.post.insert.option_cart_item_order_product';
    const PRE_UPDATE = 'propel.pre.update.option_cart_item_order_product';
    const POST_UPDATE = 'propel.post.update.option_cart_item_order_product';
    const PRE_DELETE = 'propel.pre.delete.option_cart_item_order_product';
    const POST_DELETE = 'propel.post.delete.option_cart_item_order_product';

    /** @var OptionCartItemOrderProduct */
    protected $model;

    /**
     * @param OptionCartItemOrderProduct|ActiveRecordInterface $optionCartItemOrderProduct
     */
    public function __construct(OptionCartItemOrderProduct $optionCartItemOrderProduct)
    {
        $this->model = $optionCartItemOrderProduct;
    }

    /**
     * @return OptionCartItemOrderProduct|ActiveRecordInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
