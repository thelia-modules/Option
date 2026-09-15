<?php

declare(strict_types=1);

namespace Option\Api\Resource;

use ApiPlatform\Metadata\Operation;
use Option\Model\Map\OptionProductTableMap;
use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Attribute\Groups;
use Thelia\Api\Resource\Cart;
use Thelia\Api\Resource\CartItem;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\ResourceAddonInterface;
use Thelia\Api\Resource\ResourceAddonTrait;
use Thelia\Model\Lang;
use Thelia\Model\Map\ProductI18nTableMap;
use Thelia\Model\Map\ProductTableMap;

/**
 * Exposes the options carried by a cart item on the cart payloads of the core.
 *
 * The addon short name is the key the serializer writes, so a cart item reads
 * "CartItemOptions": {"options": [...]}.
 */
class CartItemOptions implements ResourceAddonInterface
{
    use ResourceAddonTrait;

    /**
     * @var array<int, array{optionId: ?int, ref: ?string, title: ?string, price: float, taxedPrice: float, quantity: float, customization: array<string, mixed>}>
     */
    #[Groups([
        CartItem::GROUP_ADMIN_READ,
        CartItem::GROUP_FRONT_READ,
        Cart::GROUP_ADMIN_READ,
        Cart::GROUP_FRONT_READ,
    ])]
    public array $options = [];

    public static function getResourceParent(): string
    {
        return CartItem::class;
    }

    /**
     * A cart item carries a collection of options, not one row: the default
     * implementation of the trait joins a single related table and reads it
     * through virtual columns, which would repeat the cart item once per option
     * it holds. The rows are resolved in buildFromModel() instead.
     */
    public static function extendQuery(ModelCriteria $query, ?Operation $operation = null, array $context = []): void
    {
    }

    /**
     * @param \Thelia\Model\CartItem $activeRecord
     */
    public function buildFromModel(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): ResourceAddonInterface
    {
        $locale = $this->serializationLocale();

        $rows = OptionCartItemOrderProductQuery::create()
            ->filterByCartItemOptionId($activeRecord->getId())
            ->useProductAvailableOptionQuery()
                ->useOptionProductQuery()
                    ->withColumn(OptionProductTableMap::COL_ID, 'option_id')
                    ->useProductQuery()
                        ->withColumn(ProductTableMap::COL_REF, 'option_ref')
                        ->useProductI18nQuery(joinType: Criteria::LEFT_JOIN)
                            ->filterByLocale($locale)
                            ->withColumn(ProductI18nTableMap::COL_TITLE, 'option_title')
                        ->endUse()
                    ->endUse()
                ->endUse()
            ->endUse()
            ->find();

        $this->options = array_map(
            static fn (OptionCartItemOrderProduct $row): array => [
                'optionId' => $row->hasVirtualColumn('option_id') ? (int) $row->getVirtualColumn('option_id') : null,
                'ref' => $row->hasVirtualColumn('option_ref') ? (string) $row->getVirtualColumn('option_ref') : null,
                'title' => $row->hasVirtualColumn('option_title') ? $row->getVirtualColumn('option_title') : null,
                'price' => (float) $row->getPrice(),
                'taxedPrice' => (float) $row->getTaxedPrice(),
                'quantity' => (float) $row->getQuantity(),
                'customization' => json_decode((string) $row->getCustomizationData(), true) ?? [],
            ],
            iterator_to_array($rows),
        );

        return $this;
    }

    public function buildFromArray(array $data, PropelResourceInterface $abstractPropelResource): ResourceAddonInterface
    {
        return $this;
    }

    /**
     * Options are written through the option endpoints, which validate the
     * customization form of each option and adjust the price of the cart item.
     * Accepting them here would persist unvalidated input and leave the line at
     * a price that does not cover what it carries.
     */
    public function doSave(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): void
    {
        throw new BadRequestHttpException('Cart item options are read-only on this resource.');
    }

    /**
     * Called on every delete of a cart item, whether or not the payload
     * mentioned the addon. The foreign key only nulls the column, so the rows
     * are removed here rather than left behind pointing at nothing.
     *
     * @param \Thelia\Model\CartItem $activeRecord
     */
    public function doDelete(ActiveRecordInterface $activeRecord, PropelResourceInterface $abstractPropelResource): void
    {
        OptionCartItemOrderProductQuery::create()
            ->filterByCartItemOptionId($activeRecord->getId())
            ->delete();
    }

    /**
     * Same rule as the core: the requested locale when it is active, the
     * default language otherwise.
     */
    private function serializationLocale(): string
    {
        $requested = $this->getContext()['filters']['locale'] ?? null;

        foreach (Lang::getActiveLangs() as $lang) {
            if ($requested === $lang->getLocale()) {
                return $lang->getLocale();
            }
        }

        return Lang::getDefaultLanguage()->getLocale();
    }
}
