<?php

namespace Option\Model\Event;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Event\ActiveRecordEvent;
use Option\Model\CategoryAvailableOption;

class CategoryAvailableOptionEvent extends ActiveRecordEvent
{
    const PRE_SAVE = 'propel.pre.save.category_available_option';
    const POST_SAVE = 'propel.post.save.category_available_option';
    const PRE_INSERT = 'propel.pre.insert.category_available_option';
    const POST_INSERT = 'propel.post.insert.category_available_option';
    const PRE_UPDATE = 'propel.pre.update.category_available_option';
    const POST_UPDATE = 'propel.post.update.category_available_option';
    const PRE_DELETE = 'propel.pre.delete.category_available_option';
    const POST_DELETE = 'propel.post.delete.category_available_option';

    /** @var CategoryAvailableOption */
    protected $model;

    /**
     * @param CategoryAvailableOption|ActiveRecordInterface $categoryAvailableOption
     */
    public function __construct(CategoryAvailableOption $categoryAvailableOption)
    {
        $this->model = $categoryAvailableOption;
    }

    /**
     * @return CategoryAvailableOption|ActiveRecordInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
