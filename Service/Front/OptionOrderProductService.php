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
use Option\Service\OptionLineResolver;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Lang;
use Thelia\Model\Order;
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
    // Null outside an HTTP request: an order placed from the command line has no request
    // to read, and a non-nullable property would fail when the service is built.
    protected ?Request $request;

    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        Translator $translator,
        protected OptionLineResolver $optionLineResolver,
    ) {
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
            // An option that already became an order line is already invoiced. The
            // subtraction at the end of this method is absolute, not replayable: billing
            // the same option twice would take its amount off the host line twice.
            ->filterByOptionOrderProductId(null, Criteria::ISNULL)
            ->find();

        $customizations = $customizations->getData();

        foreach ($customizations as $customization) {
            $amounts = $this->createCustomizationOrderProduct(
                $placedOrder,
                $orderProduct,
                $product,
                $customization,
                $forceUntaxed
            );

            // The option can no longer be named or priced. Its line is dropped, and
            // nothing is taken off the host line for it — the customer is charged the
            // product, which is the only figure still standing.
            if (null === $amounts) {
                continue;
            }

            [$customizationUntaxedPrice, $customizationVAT] = $amounts;

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
     * The untaxed amount and the VAT the option line carries, or null when the option
     * can no longer be named or priced and its line has to be dropped.
     *
     * @return array{0: float|int, 1: float|int}|null
     *
     * @throws PropelException
     */
    public function createCustomizationOrderProduct(
        Order $placedOrder,
        OrderProduct $orderProductMaster,
        Product $product,
        OptionCartItemOrderProduct $customization,
        $forceUntaxed = 0,
    ): ?array {
        $resolved = $this->optionLineResolver->resolve($customization);

        if (null === $resolved) {
            return null;
        }

        $locale = $this->currentLocale();
        $product->setLocale($locale);

        $title = $resolved->product->setLocale('fr_FR')->getTitle();

        // Copied, never recomputed. Both figures were settled when the visitor picked the
        // option, with the tax rule of the product it hangs under and the delivery country
        // of that moment — and that is the basis the cart total, and therefore the host
        // order line, were built on. Working them out again here from the shop-wide
        // customisation rule would take an amount off the host line that was never added
        // to it, and the shop would collect something other than what it displayed.
        $untaxedPrice = (float) $customization->getPrice();
        $taxedPrice = (float) $customization->getTaxedPrice();
        $VAT = max(0.0, $taxedPrice - $untaxedPrice);

        if ($forceUntaxed) {
            // The host line carries no tax, so neither does its option. The untaxed amount
            // stands as it is: swapping in the taxed one would take a tax-inclusive figure
            // off a tax-exclusive line.
            $VAT = 0.0;
        }

        // Nothing above depends on it any more — the rule is read for the label alone, and
        // a shop whose customisation rule points nowhere gets an unlabelled line, not a
        // failed order.
        $taxRule = $this->getCustomizationTaxeRule($product);
        $taxI18n = null !== $taxRule
            ? I18n::forceI18nRetrieving($locale, 'TaxRule', $taxRule->getId())
            : null;

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
            ->setTaxRuleTitle($taxI18n?->getTitle())
            ->setTaxRuleDescription('')
            ->setEanCode(null)
            ->setCartItemId(null)
            ->save();

        (new OrderProductTax())
            ->setOrderProductId($orderProduct->getId())
            ->setTitle($taxI18n?->getTitle() ?? '')
            ->setDescription($taxI18n?->getDescription())
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
     * Request::getSession() throws when nothing set a session, which an order placed
     * outside HTTP never does — so the session is asked for only once it is known to be
     * there, and the shop's default language answers otherwise.
     */
    private function currentLocale(): string
    {
        $session = $this->request?->hasSession() ? $this->request->getSession() : null;

        return $session instanceof Session
            ? $session->getLang()->getLocale()
            : Lang::getDefaultLanguage()->getLocale();
    }

    public function getCustomizationTaxeRule(?Product $product = null): ?TaxRule
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
