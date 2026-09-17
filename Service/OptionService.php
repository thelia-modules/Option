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

namespace Option\Service;

use Option\Event\CheckOptionEvent;
use Option\Model\ProductAvailableOptionQuery;
use Option\Option as OptionModule;
use Symfony\Component\Form\Form;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Product\ProductDeleteEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;

/**
 * One option is identical to the Thelia product model.
 * There is a link table that identifies a product as an option.
 *
 * OptionProductCreateEvent extend ProductCreateEvent, it uses to identify an option creation.
 */
class OptionService
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected OptionProvider $optionProvider,
        protected TaxEngine $taxEngine,
    ) {
    }

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
            throw new \LogicException(Translator::getInstance()->trans('No Option was updated.', [], OptionModule::DOMAIN_NAME));
        }
    }

    public function deleteOption(int $productId): void
    {
        $this->dispatcher->dispatch(new ProductDeleteEvent($productId), TheliaEvents::PRODUCT_DELETE);
    }

    /**
     * @throws \Exception
     */
    public function getOptionCategory($locale = 'en_US'): Category
    {
        // The stored id outlives the row it points at: deleting the category from the
        // catalogue leaves the config value behind, findPk() then answers null against
        // a Category return type, and the TypeError takes down every hook of the module
        // without a word on screen.
        if ($optionCategoryId = OptionModule::getConfigValue(OptionModule::OPTION_CATEGORY_ID)) {
            if (null !== $optionCategory = CategoryQuery::create()->findPk($optionCategoryId)) {
                return $optionCategory;
            }
        }

        // The title is a technical sentinel, identical in every language: filtering on
        // a locale here would only miss the row the module wrote under another one.
        $optionCategory = CategoryQuery::create()
            ->useCategoryI18nQuery()
                ->filterByTitle(OptionModule::OPTION_CATEGORY_TITLE)
            ->endUse()
        ->findOne();

        if (null !== $optionCategory) {
            OptionModule::setConfigValue(OptionModule::OPTION_CATEGORY_ID, (string) $optionCategory->getId());

            return $optionCategory;
        }

        return $this->createOptionCategory($locale);
    }

    /**
     * @throws \Exception
     */
    public function createOptionCategory($locale = 'en_US', $parent = 0): Category
    {
        try {
            $optionCategory = (new Category())
                ->setLocale($locale)
                ->setParent($parent)
                ->setVisible(0)
                ->setTitle(OptionModule::OPTION_CATEGORY_TITLE);

            $optionCategory->save();

            OptionModule::setConfigValue(OptionModule::OPTION_CATEGORY_ID, (string) $optionCategory->getId());

            return $optionCategory;
        } catch (\Exception $ex) {
            throw new \Exception(\sprintf('Error during option category creation %s', $ex->getMessage()));
        }
    }

    /**
     * Retrieves and returns the list of products (which are options) attached to the product passed in parameter.
     * If the option id is specified, returns only the corresponding product in the product table.
     *
     * @param null $optionProduct
     */
    public function getProductAvailableOptions(Product $product, $optionProduct = null): ?array
    {
        $productAvailableOptions = ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId());

        if ($optionProduct) {
            $productAvailableOptions->filterByOptionId($optionProduct->getId());
        }

        $options = array_map(static fn ($productAvailableOption) => $productAvailableOption->getOptionProduct(), iterator_to_array($productAvailableOptions->find()));

        $event = new CheckOptionEvent();
        $event
            ->setIsValid(true)
            ->setOptions($options)
            ->setProduct($product);

        $this->dispatcher->dispatch($event, CheckOptionEvent::OPTION_CHECK_IS_VALID);

        return false === $event->isValid() ? [] : $event->getOptions();
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
