<?php

namespace Option\Event;

use Thelia\Core\Event\ActionEvent;

class OptionFormValidationEvent extends ActionEvent
{
    const OPTION_FORM_IS_VALID = 'option_form_is_valid';

    /** @var array */
    protected $optionsFormData;

    /**
     * @param array $optionFormData
     * @return OptionFormValidationEvent
     */
    public function setOptionsFormData(array $optionFormData): OptionFormValidationEvent
    {
        $this->optionsFormData = $optionFormData;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptionsFormData(): array|null
    {
        return $this->optionsFormData;
    }
}