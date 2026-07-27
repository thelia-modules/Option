<?php

declare(strict_types=1);

namespace Option\Api\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Option\Api\Resource\Option;
use Option\Model\Map\OptionProductTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\Collection;
use Thelia\Api\Bridge\Propel\Extension\QueryResultCollectionExtensionInterface;
use Thelia\Api\Bridge\Propel\Service\ApiResourcePropelTransformerService;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;

class OptionProductProvider implements ProviderInterface
{
    public function __construct(
        private ApiResourcePropelTransformerService $apiResourceService,
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
        $query = ProductQuery::create()
            ->useOptionProductQuery()
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

        return $this->productToOptionResource($product, $resourceClass, $context, LangQuery::create()->filterByActive(true)->find());
    }

    private function provideCollection(Operation $operation, array $context = []): array
    {
        $resourceClass = $operation->getClass();
        
        $query = ProductQuery::create()
            ->useOptionProductQuery()
                ->filterById(null, Criteria::ISNOTNULL)
                ->withColumn(OptionProductTableMap::COL_ID, 'option_id')
            ->endUse();

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
            function (Product $product) use ($resourceClass, $context, $langs) {
                return $this->productToOptionResource($product, $resourceClass, $context, $langs);
            },
            iterator_to_array($results)
        );
    }

    private function productToOptionResource(
        Product $product,
        string $resourceClass,
        array $context,
        Collection $langs
    )
    {
        $apiResource = new Option();
        $apiResource->setId($product->getVirtualColumn('option_id'))
            ->setRef($product->getRef())
            ->setTaxRuleId($product->getTaxRuleId());

        $pse = $product->getDefaultSaleElements();
        $price = ProductPriceQuery::create()->filterByProductSaleElements($pse)->findOne();

        $apiResource->setPrice((float) $price->getPrice())
            ->setPromoPrice((float) $price->getPromoPrice())
            ->setPromo((bool) $pse->getPromo())
            ->setWeight((float) $pse->getWeight())
            ->setQuantity((int) $pse->getQuantity())
            ->setVirtual((bool) $product->getVirtual())
            ->setVisible((bool) $product->getVisible());

        $reflector = new \ReflectionClass($resourceClass);

        // manageTranslatableResource() is not part of the service public API; call it via
        // reflection to preserve the original i18n hydration behavior.
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

        return $apiResource;
    }
}