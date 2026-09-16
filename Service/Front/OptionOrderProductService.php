<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Option\Service\Front;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Domain\Taxation\TaxEngine\Calculator;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductTax;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\TaxRule;
use Thelia\Model\TaxRuleQuery;
use Thelia\Tools\I18n;

class OptionOrderProductService
{
    protected Translator $translator;
    protected EventDispatcherInterface $disptacher;
    protected Request $request;

    public function __construct(RequestStack $requestStack, EventDispatcherInterface $dispatcher, Translator $translator)
    {
        $this->translator = $translator;
        $this->disptacher = $dispatcher;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @throws PropelException
     */
    public function handleOrderProduct(OrderProduct $orderProduct): void
    {
        $totalCustomizationUntaxedPrice = 0;
        $totalCustomizationVAT = 0;

        /** @var OrderProductTax $orderProductTax */
        $orderProductTax = $orderProduct->getOrderProductTaxes()->getFirst();

        $placedOrder = $orderProduct->getOrder();
        $product = ProductQuery::create()->filterByRef($orderProduct->getProductRef())->findOne();

        // prevent event loop on OrderProductEvent::POST_SAVE
        if (!$product) {
            return;
        }

        $forceUntaxed = 0;
        if (!$orderProductTax) {
            $forceUntaxed = 1;
        }

        $customizations = OptionCartItemOrderProductQuery::create()
            ->filterByOrderProductId($orderProduct->getId())
            ->find();

        $customizations = $customizations->getData();

        foreach ($customizations as $customization) {
            [$customizationUntaxedPrice, $customizationVAT] = $this->createCustomizationOrderProduct(
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
        $orderProductUntaxedPromoPrice = $orderProduct->getPromoPrice();

        // Every amount below maps a DECIMAL column, whose generated setter takes ?string.
        // Subtracting a float from what the getter returned yields a float, which the
        // setter refuses outright: casting back is not cosmetic, it is what makes the
        // call legal.
        if ($orderProductTax) {
            $orderProductTaxAmount = (float) $orderProductTax->getAmount();
            $orderProductTaxAmountPromo = (float) $orderProductTax->getPromoAmount();
            $orderProductTax
                ->setAmount((string) ($orderProductTaxAmount - $totalCustomizationVAT))
                ->setPromoAmount((string) ($orderProductTaxAmountPromo - $totalCustomizationVAT))
                ->save();
        }
        $orderProduct
            ->setPrice((string) ((float) $orderProductUntaxedPrice - $totalCustomizationUntaxedPrice))
            ->setPromoPrice((string) ((float) $orderProductUntaxedPromoPrice - $totalCustomizationUntaxedPrice))
            ->save();
    }

    /**
     * @throws PropelException
     */
    public function createCustomizationOrderProduct(
        Order $placedOrder,
        OrderProduct $orderProductMaster,
        Product $product,
        OptionCartItemOrderProduct $customization,
        $forceUntaxed = 0,
    ): array {
        $session = $this->request->getSession();
        $locale = $session instanceof \Thelia\Core\HttpFoundation\Session\Session
            ? $session->getLang()->getLocale()
            : \Thelia\Model\Lang::getDefaultLanguage()->getLocale();
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

        $taxI18n = I18n::forceI18nRetrieving($locale, 'TaxRule', $taxRule->getId());

        $orderProductMasterQuantity = $orderProductMaster->getQuantity();

        $orderProduct = new OrderProduct();
        $orderProduct
            ->setOrderId($placedOrder->getId())
            ->setProductRef('Personalisation')
            ->setProductSaleElementsRef('CUSTOMIZATION')
            ->setProductSaleElementsId(null)
            ->setTitle($title)
            ->setChapo(null)
            ->setDescription(null)
            ->setPostscriptum(null)
            ->setVirtual(1)
            ->setVirtualDocument(null)
            ->setQuantity($orderProductMasterQuantity)
            ->setPrice((string) $untaxedPrice)
            ->setPromoPrice((string) $untaxedPrice)
            ->setWasNew(0)
            ->setWasInPromo(0)
            ->setWeight('0')
            ->setTaxRuleTitle($taxI18n->getTitle())
            ->setTaxRuleDescription('')
            ->setEanCode(null)
            ->setCartItemId(null)
            ->save();

        (new OrderProductTax())
            ->setOrderProductId($orderProduct->getId())
            ->setTitle($taxI18n->getTitle())
            ->setDescription($taxI18n->getDescription())
            ->setAmount((string) $VAT)
            ->setPromoAmount((string) $VAT)
            ->save();

        $this->updateCustomizationData($orderProduct->getId(), $customization);

        return [
            $untaxedPrice,
            $VAT,
        ];
    }

    /**
     * @throws PropelException
     */
    public function updateCustomizationData($customizationOrderProductId, $customisation)
    {
        $customization = OptionCartItemOrderProductQuery::create()->filterById($customisation->getId())->findOne();
        $customization?->setOptionOrderProductId($customizationOrderProductId)->save();

        return null;
    }

    /**
     * @throws PropelException
     */
    public function getCustomizationUntaxedPrice(Order $placedOrder, TaxRule $taxRule, $taxedPrice): float|int|null
    {
        $address = OrderAddressQuery::create()->findPk($placedOrder->getDeliveryOrderAddressId());

        if (null === $taxedPrice) {
            return null;
        }

        return (new Calculator())
            ->loadTaxRuleWithoutProduct($taxRule, $address->getCountry())
            ->getUntaxedPrice($taxedPrice);
    }

    /**
     * @return array|mixed|TaxRule|null
     */
    public function getCustomizationTaxeRule(?Product $product = null): mixed
    {
        $taxRule = TaxRuleQuery::create()
            ->filterById(ConfigQuery::read('tax_customization_default_id', 1))
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
