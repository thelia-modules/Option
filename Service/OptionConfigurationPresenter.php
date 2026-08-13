<?php

declare(strict_types=1);

namespace Option\Service;

use Option\Form\OptionCreationForm;
use Option\Model\OptionProductQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\TaxRuleQuery;

/**
 * Builds the template variables of the option-configuration screen, shared by the
 * module-configuration hook (/admin/module/Option) and the standalone /admin/option page.
 */
class OptionConfigurationPresenter
{
    public function __construct(
        protected OptionService $optionService,
        protected TheliaFormFactory $formFactory,
    ) {
    }

    /**
     * @throws PropelException
     */
    public function present(): array
    {
        $optionCategory = $this->optionService->getOptionCategory();
        $locale = $this->optionService->getAdminEditionLocale();

        $options = [];
        foreach (OptionProductQuery::create()->find() as $optionProduct) {
            $product = $optionProduct->getProduct();
            if (null === $product) {
                continue;
            }

            $product->setLocale($locale);

            $options[] = [
                'id' => $product->getId(),
                'ref' => $product->getRef(),
                'title' => $product->getTitle(),
                'visible' => (bool) $product->getVisible(),
                'price' => $this->optionService->resolveDefaultPrice($product),
            ];
        }

        $defaultCurrency = CurrencyQuery::create()->filterByByDefault(1)->findOne();

        $taxRules = [];
        foreach (TaxRuleQuery::create()->orderById()->find() as $taxRule) {
            $taxRules[] = [
                'id' => $taxRule->getId(),
                'title' => $taxRule->setLocale($locale)->getTitle(),
                'isDefault' => (bool) $taxRule->getIsDefault(),
            ];
        }

        return [
            'category_id' => $optionCategory->getId(),
            'options' => $options,
            'creationForm' => $this->formFactory->createForm(OptionCreationForm::getName(), FormType::class)->createView()->getView(),
            'tax_rules' => $taxRules,
            'currency_id' => null !== $defaultCurrency ? $defaultCurrency->getId() : 0,
            'currency_symbol' => null !== $defaultCurrency ? $defaultCurrency->getSymbol() : '',
            'locale' => $locale,
        ];
    }
}
