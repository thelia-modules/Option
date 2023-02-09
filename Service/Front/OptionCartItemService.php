<?php

namespace Option\Service\Front;

use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\CartItem;
use Thelia\Model\Product;
use Thelia\TaxEngine\Calculator;
use Thelia\TaxEngine\TaxEngine;

class OptionCartItemService
{
    protected $request;
    protected $optionService;
    protected $taxEngine;

    public function __construct(RequestStack $request, OptionService $optionService, TaxEngine $taxEngine)
    {
        $this->request = $request->getCurrentRequest();
        $this->optionService = $optionService;
        $this->taxEngine = $taxEngine;
    }

    /**
     * @param CartItem $cartItem
     * @throws PropelException
     */
    public function handleCartItemOptionPrice(CartItem $cartItem): void
    {
        $options = $this->getOptionsByCartItem($cartItem);

        //Calculate option HT price with cartitam tax rule.
        $taxCalculator = $this->getTaxCalculator($cartItem);

        $totalCustomizationPrice = 0;
        $totalCustomizationPromoPrice = 0;

        /** @var Product $option */
        foreach ($options as $option) {
            $totalCustomizationPrice += $taxCalculator->getUntaxedPrice($this->optionService->getOptionTaxedPrice($option));
            $totalCustomizationPromoPrice += $taxCalculator->getUntaxedPrice($this->optionService->getOptionTaxedPrice($option, true));
        }

        $cartItem
            ->setPrice((float)$cartItem->getPrice() + $totalCustomizationPrice)
            ->setPromoPrice((float)$cartItem->getPromoPrice() + $totalCustomizationPromoPrice)
            ->save();
    }

    /**
     * @param CartItem $cartItem
     * @param null $optionId
     * @return array | Product
     * @throws PropelException
     */
    public function getOptionsByCartItem(CartItem $cartItem): array
    {
        $options = [];
        $productAvailableOptions = ProductAvailableOptionQuery::create()
            ->filterByProductId($cartItem->getProduct()->getId());

        /** @var ProductAvailableOption $productAvailableOption */
        foreach ($productAvailableOptions->find() as $productAvailableOption) {
            $options[] = $productAvailableOption->getOptionProduct()->getProduct();
        }

        return $options;
    }

    /**
     * @param CartItem $cartItem
     * @param OptionProduct $option
     * @param $formData
     * @throws PropelException
     */
    public function persistCartItemCustomizationData(CartItem $cartItem, OptionProduct $optionProduct, $formData)
    {
        $customizationCartItemData = OptionCartItemCustomizationQuery::create()
            ->filterByCartItemId($cartItem->getId())
            ->useProductAvailableOptionQuery()
                ->filterByOptionId($optionProduct->getId())
            ->endUse()
            ->findOne();

        if (!$customizationCartItemData) {
            $productAvailableOption = ProductAvailableOptionQuery::create()
                ->filterByOptionId($optionProduct->getId())
                ->filterByProduct($cartItem->getProduct())
            ->findOne();

            $customizationCartItemData = new OptionCartItemCustomization();
            $customizationCartItemData
                ->setCartItemId($cartItem->getId())
                ->setProductAvailableOptionId($productAvailableOption->getId());
        }

        $fields = ['optionId', 'optionCode'];

        $customization = array_filter($formData, function ($key) use ($fields) {
            return !in_array($key, $fields) ? true : false;
        }, ARRAY_FILTER_USE_KEY);

        $taxCalculator = $this->getTaxCalculator($cartItem);
        $price = $this->optionService->getOptionTaxedPrice($optionProduct->getProduct());
        $untaxedPrice = $taxCalculator->getUntaxedPrice($price);

        $customizationCartItemData
            ->setPrice($untaxedPrice)
            ->setTaxedPrice($price)
            ->setCustomisationData(json_encode($customization))
            ->save();
    }

    /**
     * @param $cartItem
     * @return Calculator
     * @throws PropelException
     */
    private function getTaxCalculator($cartItem): Calculator
    {
        $taxCalculator = new Calculator();
        $taxCalculator->load($cartItem->getProduct(), $this->taxEngine->getDeliveryCountry(), $this->taxEngine->getDeliveryState());
        return $taxCalculator;
    }
}