<?php

declare(strict_types=1);

namespace Option\Service;

use Exception;
use LogicException;
use Option\Event\CheckOptionEvent;
use Option\Model\ProductAvailableOptionQuery;
use Option\Option as OptionModule;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Lang;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;
use Thelia\Model\ProductPriceQuery;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;

/**
 *
 * One option is identical to the Thelia product model.
 * There is a link table that identifies a product as an option
 *
 * OptionProductCreateEvent extend ProductCreateEvent, it uses to identify an option creation.
 *
 */
class OptionService
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected OptionProvider           $optionProvider,
        protected TaxEngine $taxEngine,
        protected RequestStack $requestStack
    )
    {}

    public function createOption(Form $form): void
    {
        $createEvent = $this->optionProvider->getCreationEvent($form->getData());
        $createEvent->bindForm($form);

        $this->dispatcher->dispatch($createEvent, TheliaEvents::PRODUCT_CREATE);
    }

    public function updateOption(Form $form): void
    {
        $data = $form->getData();
        $changeEvent = $this->optionProvider->getUpdateEvent($data);

        $changeEvent->bindForm($form);

        $this->dispatcher->dispatch($changeEvent, TheliaEvents::PRODUCT_UPDATE);

        if (!$changeEvent->hasProduct()) {
            throw new LogicException(
                Translator::getInstance()->trans('No Option was updated.')
            );
        }
    }

    public function deleteOption(int $productId): void
    {
        $this->dispatcher->dispatch(new ProductDeleteEvent($productId), TheliaEvents::PRODUCT_DELETE);
    }

    /**
     * @throws Exception
     */
    public function getOptionCategory($locale = 'en_US'): Category
    {
        if ($optionCategoryId = OptionModule::getConfigValue(OptionModule::OPTION_CATEGORY_ID)) {
            return CategoryQuery::create()->findPk($optionCategoryId);
        }

        $optionCategory = CategoryQuery::create()
            ->useCategoryI18nQuery()
                ->filterByTitle(OptionModule::OPTION_CATEGORY_TITLE)
                ->filterByLocale($locale)
            ->endUse()
        ->findOne();

        return $optionCategory ?? $this->createOptionCategory(OptionModule::OPTION_CATEGORY_TITLE);
    }

    /**
     * @throws Exception
     */
    public function createOptionCategory($title, $locale = 'en_US', $parent = 0): Category
    {
        try {
            $optionCategory = (new Category())
                ->setLocale($locale)
                ->setParent($parent)
                ->setVisible(0)
                ->setTitle($title);

            $optionCategory->save();

            OptionModule::setConfigValue(OptionModule::OPTION_CATEGORY_ID, (string) $optionCategory->getId());
            return $optionCategory;

        } catch (Exception $ex) {
            throw new Exception(sprintf("Error during option category creation %s", $ex->getMessage()));
        }
    }

    /**
     * Retrieves and returns the list of products (which are options) attached to the product passed in parameter.
     * If the option id is specified, returns only the corresponding product in the product table.
     *
     * @param Product $product
     * @param null $optionProduct
     * @return array|null
     */
    public function getProductAvailableOptions(Product $product, $optionProduct = null): ?array
    {
        $productAvailableOptions = ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId());

        if ($optionProduct) {
            $productAvailableOptions->filterByOptionId($optionProduct->getId());
        }

        $options = array_map(static function ($productAvailableOption) {
            return $productAvailableOption->getOptionProduct();
        }, iterator_to_array($productAvailableOptions->find()));

        $event = new CheckOptionEvent();
        $event
            ->setIsValid(true)
            ->setOptions($options)
            ->setProduct($product);

        $this->dispatcher->dispatch($event, CheckOptionEvent::OPTION_CHECK_IS_VALID);

        return false === $event->isValid() ? [] : $event->getOptions();
    }

    /**
     * Untaxed price of the product default sale element, in the lowest currency id available.
     * Used by the back-office listings, where no customer tax context exists.
     */
    public function resolveDefaultPrice(Product $product): ?float
    {
        $productPrice = ProductPriceQuery::create()
            ->filterByProductSaleElements($product->getDefaultSaleElements())
            ->orderByCurrencyId(Criteria::ASC)
            ->findOne();

        // Propel returns DECIMAL columns as strings.
        return null !== $productPrice ? (float) $productPrice->getPrice() : null;
    }

    /**
     * Locale currently selected in the back-office language switcher.
     * Falls back to the shop default language outside of an admin session (CLI, front-office).
     */
    public function getAdminEditionLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request && $request->hasSession() ? $request->getSession() : null;

        if ($session instanceof Session) {
            return $session->getAdminEditionLang()->getLocale();
        }

        return Lang::getDefaultLanguage()->getLocale();
    }

    public function getOptionPrice(Product $option, bool $isPromo = false, $isTaxed = true): float|int
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

        if (!$isTaxed) {
            return (float) $optionPrice;
        }

        return $option->getTaxedPrice($taxCountry, $optionPrice, $taxState);
    }

    public function getOptionTaxedPrice(Product $option, bool $isPromo = false): float|int
    {
        return $this->getOptionPrice($option, $isPromo);
    }


    public function getOptionUnTaxedPrice(Product $option, bool $isPromo = false): float|int
    {
        return $this->getOptionPrice($option, $isPromo, false);
    }
}
