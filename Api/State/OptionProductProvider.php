<?php

declare(strict_types=1);

namespace Option\Api\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Option\Api\Resource\Option;
use Option\Model\Map\OptionProductTableMap;
use Option\Service\OptionService;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\Collection;
use Thelia\Api\Bridge\Propel\Extension\QueryResultCollectionExtensionInterface;
use Thelia\Api\Bridge\Propel\Service\ApiResourcePropelTransformerService;
use Thelia\Log\Tlog;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;

class OptionProductProvider implements ProviderInterface
{
    public function __construct(
        private ApiResourcePropelTransformerService $apiResourceService,
        private OptionService $optionService,
        private iterable $propelCollectionExtensions = [],
        private iterable $propelItemExtensions = []
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->provideCollection($operation, $context);
        }

        return $this->provideItem($operation, $uriVariables, $context);
    }

    private function provideItem(Operation $operation, array $uriVariables = [], array $context = [])
    {
        $resourceClass = $operation->getClass();
        $isFrontRead = $this->isFrontRead($context);

        $query = ProductQuery::create();

        if ($isFrontRead) {
            $query->filterByVisible(1);
        }

        $query->useOptionProductQuery()
                ->filterById($uriVariables['id'])
                ->withColumn(OptionProductTableMap::COL_ID, 'option_id')
            ->endUse();

        foreach ($this->propelItemExtensions as $extension) {
            $extension->applyToItem($query, $resourceClass, $operation, $context);
        }

        $product = $query->findOne();

        if (null === $product) {
            return null;
        }

        return $this->productToOptionResource($product, $resourceClass, $context, LangQuery::create()->filterByActive(true)->find(), $isFrontRead);
    }

    private function provideCollection(Operation $operation, array $context = []): array
    {
        $resourceClass = $operation->getClass();
        $isFrontRead = $this->isFrontRead($context);
        $filters = $context['filters'] ?? [];

        $productId = isset($filters['productId']) ? (int) $filters['productId'] : null;

        // The options of a product are asked for from a sale element as often as
        // from the product itself: a front knows the pse it is about to put in
        // the cart. A pse that does not exist narrows the collection to nothing
        // rather than widening it back to every option of the shop.
        if (isset($filters['pseId'])) {
            $productSaleElements = ProductSaleElementsQuery::create()->findPk((int) $filters['pseId']);

            if (null === $productSaleElements) {
                return [];
            }

            $productId = $productSaleElements->getProductId();
        }

        $query = ProductQuery::create();

        if ($isFrontRead) {
            $query->filterByVisible(1);
        }

        $optionQuery = $query->useOptionProductQuery()
            ->filterById(null, Criteria::ISNOTNULL)
            ->withColumn(OptionProductTableMap::COL_ID, 'option_id');

        if (null !== $productId) {
            $optionQuery->useProductAvailableOptionQuery()
                ->filterByProductId($productId)
                ->endUse();
        }

        $query = $optionQuery->endUse();

        $resultExtensions = [];
        foreach ($this->propelCollectionExtensions as $extension) {
            $extension->applyToCollection($query, $resourceClass, $operation, $context);

            // Keep result extension for the end to apply all join / filter before
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operation, $context)) {
                $resultExtensions[] = $extension;
            }
        }

        $results = null;
        foreach ($resultExtensions as $resultExtension) {
            $results = $resultExtension->getResult($query, $resourceClass, $operation, $context);
        }

        if (null === $results) {
            $results = $query->find();
        }

        $langs = LangQuery::create()->filterByActive(true)->find();
        return array_map(
            function (Product $product) use ($resourceClass, $context, $langs, $isFrontRead) {
                return $this->productToOptionResource($product, $resourceClass, $context, $langs, $isFrontRead);
            },
            iterator_to_array($results)
        );
    }

    private function productToOptionResource(
        Product $product,
        string $resourceClass,
        array $context,
        Collection $langs,
        bool $isFrontRead = false
    )
    {
        $apiResource = new Option();
        $apiResource->setId($product->getVirtualColumn('option_id'))
            ->setRef($product->getRef())
            ->setTaxRuleId($product->getTaxRuleId());

        $pse = $product->getDefaultSaleElements();
        $price = ProductPriceQuery::create()->filterByProductSaleElements($pse)->findOne();

        $apiResource->setPrice((float) $price?->getPrice())
            ->setPromoPrice((float) $price?->getPromoPrice())
            ->setPromo((bool) $pse->getPromo())
            ->setWeight((float) $pse->getWeight())
            ->setQuantity((int) $pse->getQuantity())
            ->setVirtual((bool) $product->getVirtual())
            ->setVisible((bool) $product->getVisible());

        // Only the front groups carry the taxed prices, and resolving a tax costs
        // a country, a state and a rule per option.
        if ($isFrontRead && null !== $price) {
            $apiResource->setTaxedPrice((float) $this->optionService->getOptionTaxedPrice($product))
                ->setTaxedPromoPrice((float) $this->optionService->getOptionTaxedPrice($product, true));
        }

        // @todo The core exposes no public API to hydrate the i18n of a resource built from an
        //       arbitrary model (here a Product mapped to an Option resource — modelToResource()
        //       does not fit this atypical mapping). We call the private manageTranslatableResource()
        //       via reflection. Guard against its removal so a future core refactor degrades
        //       gracefully (resource returned without translations + a logged warning) instead of
        //       throwing a ReflectionException. A public i18n-hydration entry point should be
        //       requested on the core side.
        if (method_exists($this->apiResourceService, 'manageTranslatableResource')) {
            $reflector = new \ReflectionClass($resourceClass);
            $manageTranslatable = new \ReflectionMethod($this->apiResourceService, 'manageTranslatableResource');
            $manageTranslatable->invoke(
                $this->apiResourceService,
                $resourceClass,
                $product,
                $product,
                $apiResource,
                null,
                $reflector,
                $context,
                $langs
            );
        } else {
            Tlog::getInstance()->addWarning(
                'Option: ApiResourcePropelTransformerService::manageTranslatableResource() is no longer available; '
                .'the option API resource is returned without i18n hydration.'
            );
        }

        return $apiResource;
    }

    /**
     * The serialization groups say which side of the shop is asking, and the
     * front side is the one that hides invisible options and pays for taxed
     * prices.
     */
    private function isFrontRead(array $context): bool
    {
        return \in_array(Option::GROUP_FRONT_READ, $context['groups'] ?? [], true);
    }
}
