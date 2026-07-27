<?php

namespace Option\Model\Event;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Event\ActiveRecordEvent;
use Option\Model\OptionProduct;

class OptionProductEvent extends ActiveRecordEvent
{
    const PRE_SAVE = 'propel.pre.save.option_product';
    const POST_SAVE = 'propel.post.save.option_product';
    const PRE_INSERT = 'propel.pre.insert.option_product';
    const POST_INSERT = 'propel.post.insert.option_product';
    const PRE_UPDATE = 'propel.pre.update.option_product';
    const POST_UPDATE = 'propel.post.update.option_product';
    const PRE_DELETE = 'propel.pre.delete.option_product';
    const POST_DELETE = 'propel.post.delete.option_product';

    /** @var OptionProduct */
    protected $model;

    /**
     * @param OptionProduct|ActiveRecordInterface $optionProduct
     */
    public function __construct(OptionProduct $optionProduct)
    {
        $this->model = $optionProduct;
    }

    /**
     * @return OptionProduct|ActiveRecordInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
