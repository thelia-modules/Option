<?php

namespace Option\Service\BackOffice;


use Option\Event\OptionProductCreateEvent;
use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

class OptionProductService
{
    public function setOptionProduct($productId, $optionId)
    {
        $product = ProductQuery::create()->findPk($productId);
        $option = OptionProductQuery::create()->findPk($optionId);

        ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId())
            ->filterByOptionId($option->getId())
        ->findOneOrCreate()
        ->save();
    }

    /**
     * @param $formData
     * @return OptionProductCreateEvent
     */
    public function getCreationEvent($formData)
    {
        $createEvent = new OptionProductCreateEvent();

        $createEvent
            ->setRef($formData['ref'])
            ->setTitle($formData['title'])
            ->setLocale($formData['locale'])
            ->setDefaultCategory($formData['default_category'])
            ->setVisible($formData['visible'])
            ->setVirtual($formData['virtual'])
            ->setBasePrice($formData['price'])
            ->setBaseWeight($formData['weight'])
            ->setCurrencyId($formData['currency'])
            ->setTaxRuleId($formData['tax_rule'])
            ->setBaseQuantity($formData['quantity'])
            ->setTemplateId($formData['template_id'])
            ->setIsOption(true)
        ;

        return $createEvent;
    }
}