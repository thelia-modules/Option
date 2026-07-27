<?php

declare(strict_types=1);

namespace Option\Hook\Back;

use Exception;
use Option\Form\OptionCreationForm;
use Option\Model\OptionProductQuery;
use Option\Service\OptionService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Lang;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\TaxRuleQuery;

class ConfigurationHook extends BaseHook
{
    public function __construct(
        protected OptionService $optionService,
        protected TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigurationJs'],
            ],
            'main.in-top-menu-items' => [
                ['type' => 'back', 'method' => 'onMainTopMenuTools'],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $optionCategory = $this->optionService->getOptionCategory();
        $locale = $this->getCurrentLocale();

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
                'price' => $this->resolveOptionPrice($product),
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

        $event->add(
            $this->render('Option/option-configuration.html.twig', [
                'category_id' => $optionCategory->getId(),
                'options' => $options,
                'creationForm' => $this->formFactory->createForm(OptionCreationForm::getName(), FormType::class)->createView()->getView(),
                'tax_rules' => $taxRules,
                'currency_id' => null !== $defaultCurrency ? $defaultCurrency->getId() : 0,
                'currency_symbol' => null !== $defaultCurrency ? $defaultCurrency->getSymbol() : '',
                'locale' => $locale,
            ])
        );
    }

    public function onModuleConfigurationJs(HookRenderEvent $event): void
    {
        $event->add(
            $this->render('Option/option-configuration.js.html.twig')
        );
    }

    public function onMainTopMenuTools(HookRenderEvent $event): void
    {
        $event->add($this->render('Option/hook/menu-hook.html.twig', $event->getArguments()));
    }

    private function getCurrentLocale(): string
    {
        $session = $this->getRequest()?->getSession();
        if ($session instanceof Session) {
            return $session->getAdminEditionLang()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }

    private function resolveOptionPrice(Product $product): ?float
    {
        $productPrice = ProductPriceQuery::create()
            ->filterByProductSaleElements($product->getDefaultSaleElements())
            ->orderByCurrencyId(Criteria::ASC)
            ->findOne();

        return null !== $productPrice ? (float) $productPrice->getPrice() : null;
    }
}
