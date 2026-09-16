<?php

declare(strict_types=1);

namespace Option\Event;

use Thelia\Core\Event\Product\ProductCreateEvent;

class OptionProductCreateEvent extends ProductCreateEvent
{
    /** @var bool */
    protected bool $isOption;

    protected bool $isCustomizable = false;

    /**
     * @return bool|null
     */
    public function isOption(): ?bool
    {
        return $this->isOption;
    }

    /**
     * @param bool $isOption
     */
    public function setIsOption(bool $isOption): OptionProductCreateEvent
    {
        $this->isOption = $isOption;
        return $this;
    }

    public function isCustomizable(): bool
    {
        return $this->isCustomizable;
    }

    public function setIsCustomizable(bool $isCustomizable): OptionProductCreateEvent
    {
        $this->isCustomizable = $isCustomizable;
        return $this;
    }
}
