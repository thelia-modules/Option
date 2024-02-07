<?php

namespace Option\Api\Resource;

use ApiPlatform\Metadata\Operation;
use Option\Model\OptionProductQuery;
use Option\Service\OptionProductService;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Thelia\Api\Resource\Customer;
use Thelia\Api\Resource\Product;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\ResourceAddonInterface;
use Thelia\Api\Resource\ResourceAddonTrait;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Model\ProductQuery;

class ProductAvailableOption implements ResourceAddonInterface
{
    use ResourceAddonTrait;

    public ?int $id = null;

    public int $productId;

    #[Groups([Product::GROUP_ADMIN_READ, Product::GROUP_ADMIN_WRITE])]
    public string $optionRef;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ProductAvailableOption
    {
        $this->id = $id;
        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): ProductAvailableOption
    {
        $this->productId = $productId;
        return $this;
    }

    public function getOptionRef(): string
    {
        return $this->optionRef;
    }

    public function setOptionRef(string $optionRef): ProductAvailableOption
    {
        $this->optionRef = $optionRef;
        return $this;
    }

    public static function getResourceParent(): string
    {
        return Product::class;
    }

    /**
     * @param ProductQuery $query
     */
    public static function extendQuery(ModelCriteria $query, Operation $operation = null, array $context = []): void
    {
        $query->useProductAvailableOptionQuery(joinType: Criteria::LEFT_JOIN)
                ->useOptionProductQuery(joinType: Criteria::LEFT_JOIN)
                    ->useProductQuery(relationAlias: 'option_product_product', joinType: Criteria::LEFT_JOIN)
                        ->withColumn('option_product_product.ref', 'option_ref')
                    ->endUse()
                ->endUse()
            ->endUse();
    }

    public function buildFromModel(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): ResourceAddonInterface
    {
        $optionRef = $activeRecord->hasVirtualColumn('option_ref') ? $activeRecord->getVirtualColumn('option_ref') : null;

        if ($optionRef) {
            $this->optionRef = $optionRef;
        }

        return $this;
    }

    public function buildFromArray(array $data, PropelResourceInterface $abstractPropelResource): ResourceAddonInterface
    {
        $this->optionRef = $data['optionRef'];
        return $this;
    }

    /**
     * @param \Thelia\Model\Product $activeRecord
     */
    public function doSave(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): void
    {
        $model = $activeRecord->getProductAvailableOptions()->getData()[0] ?? new \Option\Model\ProductAvailableOption();

        $model->setProductId($activeRecord->getId());

        $option = OptionProductQuery::create()
            ->useProductQuery()
                ->filterByRef($this->getOptionRef())
            ->endUse()
            ->findOne();

        if ($option) {
            $model->setOptionId($option->getId())
                ->setOptionAddedBy(json_encode([OptionProductService::ADDED_BY_PRODUCT]));
            $model->save();
        }
    }

    /**
     * @param \Thelia\Model\Product $activeRecord
     */
    public function doDelete(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): void
    {
        foreach ($activeRecord->getProductAvailableOptions() as $productAvailableOption){
            $productAvailableOption->delete();
        }
    }
}