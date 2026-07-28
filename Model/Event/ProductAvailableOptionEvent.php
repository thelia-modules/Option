<?php

namespace Option\Model\Event;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Event\ActiveRecordEvent;
use Option\Model\ProductAvailableOption;

class ProductAvailableOptionEvent extends ActiveRecordEvent
{
    const PRE_SAVE = 'propel.pre.save.product_available_option';
    const POST_SAVE = 'propel.post.save.product_available_option';
    const PRE_INSERT = 'propel.pre.insert.product_available_option';
    const POST_INSERT = 'propel.post.insert.product_available_option';
    const PRE_UPDATE = 'propel.pre.update.product_available_option';
    const POST_UPDATE = 'propel.post.update.product_available_option';
    const PRE_DELETE = 'propel.pre.delete.product_available_option';
    const POST_DELETE = 'propel.post.delete.product_available_option';

    /** @var ProductAvailableOption */
    protected $model;

    /**
     * @param ProductAvailableOption|ActiveRecordInterface $productAvailableOption
     */
    public function __construct(ProductAvailableOption $productAvailableOption)
    {
        $this->model = $productAvailableOption;
    }

    /**
     * @return ProductAvailableOption|ActiveRecordInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
