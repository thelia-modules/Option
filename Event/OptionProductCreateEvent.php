<?php

namespace Option\Event;

use Thelia\Core\Event\Product\ProductCreateEvent;

class OptionProductCreateEvent extends ProductCreateEvent
{
    /** @var bool */
    protected $isOption;

    /**
     * @return bool|null
     */
    public function isOption()
    {
        return $this->isOption;
    }

    /**
     * @param bool $isOption
     */
    public function setIsOption(bool $isOption): void
    {
        $this->isOption = $isOption;
    }
}