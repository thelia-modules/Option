<?php

namespace Option\Service\Front;

use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Option\Option;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductTax;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\TaxRule;
use Thelia\Model\TaxRuleQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\I18n;

class OptionOrderProductService
{
    protected $translator;
    protected $disptacher;
    protected $request;

    public function __construct(RequestStack $requestStack, EventDispatcherInterface $disptacher, Translator $translator)
    {
        $this->translator = $translator;
        $this->disptacher = $disptacher;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function handleOrderProduct(OrderProduct $orderProduct)
    {
        $totalCustomizationUntaxedPrice = 0;
        $totalCustomizationVAT = 0;

        /** @var OrderProductTax $orderProductTax */
        $orderProductTax = $orderProduct->getOrderProductTaxes()->getFirst();

        $placedOrder = $orderProduct->getOrder();
        $product = ProductQuery::create()->filterByRef($orderProduct->getProductRef())->findOne();

        //prevent event loop on OrderProductEvent::POST_SAVE
        if (!$product) {
            return;
        }

        $forceUntaxed = 0;
        if (!$orderProductTax) {
            $forceUntaxed = 1;
        }

        $customizations = OptionCartItemCustomizationQuery::create()
            ->filterByOrderProductId($orderProduct->getId())
            ->find();

        $customizations = $customizations->getData();

        foreach ($customizations as $customization) {
            list ($customizationUntaxedPrice, $customizationVAT) = $this->createCustomizationOrderProduct(
                $placedOrder,
                $orderProduct,
                $product,
                $customization,
                $forceUntaxed
            );

            $totalCustomizationUntaxedPrice += $customizationUntaxedPrice;
            $totalCustomizationVAT += $customizationVAT;
        }

        $orderProductUntaxedPrice = $orderProduct->getPrice();

        if ($orderProductTax) {
            $orderProductTaxAmount = $orderProductTax->getAmount();
            $orderProductTax
                ->setAmount($orderProductTaxAmount - $totalCustomizationVAT)
                ->save();
        }

        $orderProduct
            ->setPrice($orderProductUntaxedPrice - $totalCustomizationUntaxedPrice)
            ->save();
    }

    public function createCustomizationOrderProduct(Order $placedOrder, OrderProduct $orderProductMaster, Product $product, OptionCartItemCustomization $customization, $forceUntaxed = 0)
    {
        $locale = $this->request->getSession()->getLang()->getLocale();
        $product->setLocale($locale);

        $title = $customization->getProductAvailableOption()->getOptionProduct()->getProduct()->setLocale('fr_FR')->getTitle();

        $taxRule = $this->getCustomizationTaxeRule($product);
        $taxedPrice = $customization->getTaxedPrice();
        $untaxedPrice = $this->getCustomizationUntaxedPrice($placedOrder, $taxRule, $customization->getTaxedPrice());

        $VAT = $taxedPrice - $untaxedPrice;
        if ($VAT < 0) {
            $VAT = 0;
        }

        if ($forceUntaxed) {
            $VAT = 0;
            $untaxedPrice = $taxedPrice;
        }

        /** @var  $taxI18n */
        $taxI18n = I18n::forceI18nRetrieving($locale, 'TaxRule', $taxRule->getId());

        $orderProductMasterQuantity = $orderProductMaster->getQuantity();

        $orderProduct = new OrderProduct();
        $orderProduct
            ->setOrderId($placedOrder->getId())
            ->setProductRef("Personalisation")
            ->setProductSaleElementsRef("CUSTOMIZATION")
            ->setProductSaleElementsId(null)
            ->setTitle($title)
            ->setChapo(null)
            ->setDescription(null)
            ->setPostscriptum(null)
            ->setVirtual(1)
            ->setVirtualDocument(null)
            ->setQuantity($orderProductMasterQuantity)
            ->setPrice($untaxedPrice)
            ->setPromoPrice($untaxedPrice)
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setWeight(0)
            ->setTaxRuleTitle($taxI18n->getTitle())
            ->setTaxRuleDescription('')
            ->setEanCode(null)
            ->setCartItemId(null)
            ->save();

        (new OrderProductTax())
            ->setOrderProductId($orderProduct->getId())
            ->setTitle($taxI18n->getTitle())
            ->setDescription($taxI18n->getDescription())
            ->setAmount($VAT)
            ->setPromoAmount($VAT)
            ->save();

        $this->updateCustomizationData($orderProduct->getId(), $customization);

        return [
            $untaxedPrice * $orderProductMasterQuantity,
            $VAT * $orderProductMasterQuantity
        ];
    }

    public function updateCustomizationData($customizationOrderProductId, $customisation)
    {
        $customization = OptionCartItemCustomizationQuery::create()->filterById($customisation->getId())->findOne();
        $customization->setDataCustomizationOrderProductId($customizationOrderProductId)->save();
        return null;
    }

    public function getCustomizationUntaxedPrice(Order $placedOrder, TaxRule $taxRule, $taxedPrice)
    {
        $address = OrderAddressQuery::create()->findPk($placedOrder->getDeliveryOrderAddressId());

        return (new Calculator())
            ->loadTaxRuleWithoutProduct($taxRule, $address->getCountry())
            ->getUntaxedPrice($taxedPrice);
    }

    /**
     * @param Product $product
     * @return array|mixed|\Thelia\Model\TaxRule|null*
     */
    public function getCustomizationTaxeRule(Product $product = null)
    {
        $taxRule = TaxRuleQuery::create()
            ->filterById(ConfigQuery::read("tax_customization_default_id", 1))
            ->findOne();

        if ($taxRule) {
            return $taxRule;
        }

        if ($product) {
            return TaxRuleQuery::create()->findPk($product->getTaxRuleId());
        }

        return null;
    }
}