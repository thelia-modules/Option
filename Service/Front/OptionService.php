<?php

namespace Option\Service\Front;

use Option\Event\CheckOptionEvent;
use Option\Model\OptionProduct;
use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\TableMap;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;
use Thelia\TaxEngine\TaxEngine;

class OptionService
{
    /** @var Request */
    protected $request;
    protected $taxEngine;
    protected $eventDispatcher;

    public function __construct(RequestStack $request, TaxEngine $taxEngine, EventDispatcherInterface $eventDispatcher)
    {
        $this->request = $request->getCurrentRequest();
        $this->taxEngine = $taxEngine;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Product $product
     * @param OptionProduct $optionProduct
     * @return mixed
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getOptionCustomization(Product $product, OptionProduct $optionProduct)
    {
        if (!$customizationSerialized = $this->getProductAvailableOptionCustomization($product, $optionProduct)) {
            $customization = OptionProductQuery::create()
                ->filterByProductId($optionProduct->getProduct()->getId())
                ->findOne();

            return json_decode($customization->getConfiguration(), true);
        }

        return json_decode($customizationSerialized, true);
    }

    public function getProductAvailableOptionCustomization(Product $product, $optionProduct)
    {
        $productAvailableOption = ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId())
            ->filterByOptionId($optionProduct->getId())
            ->findOne();

        if ($productAvailableOption) {
            return $productAvailableOption->getProductAvailableOptionCustomization();
        }

        return false;
    }

    /**
     * @param Product $product
     * @param null $optionProduct
     * @return array|ObjectCollection|null
     */
    public function getProductAvailableOptions(Product $product, $optionProduct = null)
    {
        $productAvailableOptions = ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId());

        if ($optionProduct) {
            $productAvailableOptions->filterByOptionId($optionProduct->getId());
        }

        $options = array_map(function ($productAvailableOption) {
            return $productAvailableOption->getOptionProduct();
        }, iterator_to_array($productAvailableOptions->find()));

        $event = new CheckOptionEvent();
        $event
            ->setIsValid(true)
            ->setOptions($options)
            ->setProduct($product);

        $this->eventDispatcher->dispatch($event, CheckOptionEvent::OPTION_CHECK_IS_VALID);

        return false === $event->isValid() ? [] : $event->getOptions();
    }

    public function getOptionTaxedPrice(Product $option, $isPromo = false)
    {
        $taxCountry = $this->taxEngine->getDeliveryCountry();
        $taxState = $this->taxEngine->getDeliveryState();
        $optionPse = $option->getDefaultSaleElements();

        /** @var ProductPrice $optionPseProductPrice */
        $optionPseProductPrice = $optionPse->getProductPrices()->getFirst();

        $optionPrice = $optionPseProductPrice->getPrice();
        if ($isPromo) {
            $optionPrice = $optionPseProductPrice->getPromoPrice();
        }

        return $option->getTaxedPrice($taxCountry, $optionPrice, $taxState);
    }
}