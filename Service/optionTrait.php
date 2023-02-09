<?php


namespace Option\Service;


use Option\Option;
use Thelia\Model\LangQuery;
use Thelia\Model\ModuleConfigQuery;

trait optionTrait
{
    /**
     * @return \Thelia\Model\Lang
     */
    public function getDefaultLocale()
    {
        return LangQuery::create()->filterByByDefault(1)->findOne();
    }
}