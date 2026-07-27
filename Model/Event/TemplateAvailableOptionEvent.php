<?php

namespace Option\Model\Event;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Event\ActiveRecordEvent;
use Option\Model\TemplateAvailableOption;

class TemplateAvailableOptionEvent extends ActiveRecordEvent
{
    const PRE_SAVE = 'propel.pre.save.template_available_option';
    const POST_SAVE = 'propel.post.save.template_available_option';
    const PRE_INSERT = 'propel.pre.insert.template_available_option';
    const POST_INSERT = 'propel.post.insert.template_available_option';
    const PRE_UPDATE = 'propel.pre.update.template_available_option';
    const POST_UPDATE = 'propel.post.update.template_available_option';
    const PRE_DELETE = 'propel.pre.delete.template_available_option';
    const POST_DELETE = 'propel.post.delete.template_available_option';

    /** @var TemplateAvailableOption */
    protected $model;

    /**
     * @param TemplateAvailableOption|ActiveRecordInterface $templateAvailableOption
     */
    public function __construct(TemplateAvailableOption $templateAvailableOption)
    {
        $this->model = $templateAvailableOption;
    }

    /**
     * @return TemplateAvailableOption|ActiveRecordInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}
