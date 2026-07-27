<?php

namespace Option\Model\Map;

use Option\Model\OptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'option_cart_item_order_product' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class OptionCartItemOrderProductTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Option.Model.Map.OptionCartItemOrderProductTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'TheliaMain';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'option_cart_item_order_product';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'OptionCartItemOrderProduct';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Option\\Model\\OptionCartItemOrderProduct';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Option.Model.OptionCartItemOrderProduct';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 9;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 9;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'option_cart_item_order_product.id';

    /**
     * the column name for the product_available_option_id field
     */
    public const COL_PRODUCT_AVAILABLE_OPTION_ID = 'option_cart_item_order_product.product_available_option_id';

    /**
     * the column name for the cart_item_option_id field
     */
    public const COL_CART_ITEM_OPTION_ID = 'option_cart_item_order_product.cart_item_option_id';

    /**
     * the column name for the order_product_id field
     */
    public const COL_ORDER_PRODUCT_ID = 'option_cart_item_order_product.order_product_id';

    /**
     * the column name for the option_order_product_id field
     */
    public const COL_OPTION_ORDER_PRODUCT_ID = 'option_cart_item_order_product.option_order_product_id';

    /**
     * the column name for the customization_data field
     */
    public const COL_CUSTOMIZATION_DATA = 'option_cart_item_order_product.customization_data';

    /**
     * the column name for the price field
     */
    public const COL_PRICE = 'option_cart_item_order_product.price';

    /**
     * the column name for the taxed_price field
     */
    public const COL_TAXED_PRICE = 'option_cart_item_order_product.taxed_price';

    /**
     * the column name for the quantity field
     */
    public const COL_QUANTITY = 'option_cart_item_order_product.quantity';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'ProductAvailableOptionId', 'CartItemOptionId', 'OrderProductId', 'OptionOrderProductId', 'CustomizationData', 'Price', 'TaxedPrice', 'Quantity', ],
        self::TYPE_CAMELNAME     => ['id', 'productAvailableOptionId', 'cartItemOptionId', 'orderProductId', 'optionOrderProductId', 'customizationData', 'price', 'taxedPrice', 'quantity', ],
        self::TYPE_COLNAME       => [OptionCartItemOrderProductTableMap::COL_ID, OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA, OptionCartItemOrderProductTableMap::COL_PRICE, OptionCartItemOrderProductTableMap::COL_TAXED_PRICE, OptionCartItemOrderProductTableMap::COL_QUANTITY, ],
        self::TYPE_FIELDNAME     => ['id', 'product_available_option_id', 'cart_item_option_id', 'order_product_id', 'option_order_product_id', 'customization_data', 'price', 'taxed_price', 'quantity', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['Id' => 0, 'ProductAvailableOptionId' => 1, 'CartItemOptionId' => 2, 'OrderProductId' => 3, 'OptionOrderProductId' => 4, 'CustomizationData' => 5, 'Price' => 6, 'TaxedPrice' => 7, 'Quantity' => 8, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'productAvailableOptionId' => 1, 'cartItemOptionId' => 2, 'orderProductId' => 3, 'optionOrderProductId' => 4, 'customizationData' => 5, 'price' => 6, 'taxedPrice' => 7, 'quantity' => 8, ],
        self::TYPE_COLNAME       => [OptionCartItemOrderProductTableMap::COL_ID => 0, OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID => 1, OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID => 2, OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID => 3, OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID => 4, OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA => 5, OptionCartItemOrderProductTableMap::COL_PRICE => 6, OptionCartItemOrderProductTableMap::COL_TAXED_PRICE => 7, OptionCartItemOrderProductTableMap::COL_QUANTITY => 8, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'product_available_option_id' => 1, 'cart_item_option_id' => 2, 'order_product_id' => 3, 'option_order_product_id' => 4, 'customization_data' => 5, 'price' => 6, 'taxed_price' => 7, 'quantity' => 8, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ID',
        'OptionCartItemOrderProduct.Id' => 'ID',
        'id' => 'ID',
        'optionCartItemOrderProduct.id' => 'ID',
        'OptionCartItemOrderProductTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'option_cart_item_order_product.id' => 'ID',
        'ProductAvailableOptionId' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'OptionCartItemOrderProduct.ProductAvailableOptionId' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'productAvailableOptionId' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'optionCartItemOrderProduct.productAvailableOptionId' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'COL_PRODUCT_AVAILABLE_OPTION_ID' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'product_available_option_id' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'option_cart_item_order_product.product_available_option_id' => 'PRODUCT_AVAILABLE_OPTION_ID',
        'CartItemOptionId' => 'CART_ITEM_OPTION_ID',
        'OptionCartItemOrderProduct.CartItemOptionId' => 'CART_ITEM_OPTION_ID',
        'cartItemOptionId' => 'CART_ITEM_OPTION_ID',
        'optionCartItemOrderProduct.cartItemOptionId' => 'CART_ITEM_OPTION_ID',
        'OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID' => 'CART_ITEM_OPTION_ID',
        'COL_CART_ITEM_OPTION_ID' => 'CART_ITEM_OPTION_ID',
        'cart_item_option_id' => 'CART_ITEM_OPTION_ID',
        'option_cart_item_order_product.cart_item_option_id' => 'CART_ITEM_OPTION_ID',
        'OrderProductId' => 'ORDER_PRODUCT_ID',
        'OptionCartItemOrderProduct.OrderProductId' => 'ORDER_PRODUCT_ID',
        'orderProductId' => 'ORDER_PRODUCT_ID',
        'optionCartItemOrderProduct.orderProductId' => 'ORDER_PRODUCT_ID',
        'OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID' => 'ORDER_PRODUCT_ID',
        'COL_ORDER_PRODUCT_ID' => 'ORDER_PRODUCT_ID',
        'order_product_id' => 'ORDER_PRODUCT_ID',
        'option_cart_item_order_product.order_product_id' => 'ORDER_PRODUCT_ID',
        'OptionOrderProductId' => 'OPTION_ORDER_PRODUCT_ID',
        'OptionCartItemOrderProduct.OptionOrderProductId' => 'OPTION_ORDER_PRODUCT_ID',
        'optionOrderProductId' => 'OPTION_ORDER_PRODUCT_ID',
        'optionCartItemOrderProduct.optionOrderProductId' => 'OPTION_ORDER_PRODUCT_ID',
        'OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID' => 'OPTION_ORDER_PRODUCT_ID',
        'COL_OPTION_ORDER_PRODUCT_ID' => 'OPTION_ORDER_PRODUCT_ID',
        'option_order_product_id' => 'OPTION_ORDER_PRODUCT_ID',
        'option_cart_item_order_product.option_order_product_id' => 'OPTION_ORDER_PRODUCT_ID',
        'CustomizationData' => 'CUSTOMIZATION_DATA',
        'OptionCartItemOrderProduct.CustomizationData' => 'CUSTOMIZATION_DATA',
        'customizationData' => 'CUSTOMIZATION_DATA',
        'optionCartItemOrderProduct.customizationData' => 'CUSTOMIZATION_DATA',
        'OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA' => 'CUSTOMIZATION_DATA',
        'COL_CUSTOMIZATION_DATA' => 'CUSTOMIZATION_DATA',
        'customization_data' => 'CUSTOMIZATION_DATA',
        'option_cart_item_order_product.customization_data' => 'CUSTOMIZATION_DATA',
        'Price' => 'PRICE',
        'OptionCartItemOrderProduct.Price' => 'PRICE',
        'price' => 'PRICE',
        'optionCartItemOrderProduct.price' => 'PRICE',
        'OptionCartItemOrderProductTableMap::COL_PRICE' => 'PRICE',
        'COL_PRICE' => 'PRICE',
        'option_cart_item_order_product.price' => 'PRICE',
        'TaxedPrice' => 'TAXED_PRICE',
        'OptionCartItemOrderProduct.TaxedPrice' => 'TAXED_PRICE',
        'taxedPrice' => 'TAXED_PRICE',
        'optionCartItemOrderProduct.taxedPrice' => 'TAXED_PRICE',
        'OptionCartItemOrderProductTableMap::COL_TAXED_PRICE' => 'TAXED_PRICE',
        'COL_TAXED_PRICE' => 'TAXED_PRICE',
        'taxed_price' => 'TAXED_PRICE',
        'option_cart_item_order_product.taxed_price' => 'TAXED_PRICE',
        'Quantity' => 'QUANTITY',
        'OptionCartItemOrderProduct.Quantity' => 'QUANTITY',
        'quantity' => 'QUANTITY',
        'optionCartItemOrderProduct.quantity' => 'QUANTITY',
        'OptionCartItemOrderProductTableMap::COL_QUANTITY' => 'QUANTITY',
        'COL_QUANTITY' => 'QUANTITY',
        'option_cart_item_order_product.quantity' => 'QUANTITY',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('option_cart_item_order_product');
        $this->setPhpName('OptionCartItemOrderProduct');
        $this->setIdentifierQuoting(true);
        $this->setClassName('\\Option\\Model\\OptionCartItemOrderProduct');
        $this->setPackage('Option.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('product_available_option_id', 'ProductAvailableOptionId', 'INTEGER', 'product_available_option', 'id', false, null, null);
        $this->addForeignKey('cart_item_option_id', 'CartItemOptionId', 'INTEGER', 'cart_item', 'id', false, null, null);
        $this->addForeignKey('order_product_id', 'OrderProductId', 'INTEGER', 'order_product', 'id', false, null, null);
        $this->addForeignKey('option_order_product_id', 'OptionOrderProductId', 'INTEGER', 'order_product', 'id', false, null, null);
        $this->addColumn('customization_data', 'CustomizationData', 'LONGVARCHAR', false, null, null);
        $this->addColumn('price', 'Price', 'DECIMAL', false, 16, 0.000000);
        $this->addColumn('taxed_price', 'TaxedPrice', 'DECIMAL', false, 16, 0.000000);
        $this->addColumn('quantity', 'Quantity', 'VARCHAR', false, 255, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('CartItem', '\\Thelia\\Model\\CartItem', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':cart_item_option_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
        $this->addRelation('ProductAvailableOption', '\\Option\\Model\\ProductAvailableOption', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':product_available_option_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
        $this->addRelation('OrderProductRelatedByOrderProductId', '\\Thelia\\Model\\OrderProduct', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':order_product_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
        $this->addRelation('OrderProductRelatedByOptionOrderProductId', '\\Thelia\\Model\\OrderProduct', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':option_order_product_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? OptionCartItemOrderProductTableMap::CLASS_DEFAULT : OptionCartItemOrderProductTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (OptionCartItemOrderProduct object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = OptionCartItemOrderProductTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = OptionCartItemOrderProductTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + OptionCartItemOrderProductTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = OptionCartItemOrderProductTableMap::OM_CLASS;
            /** @var OptionCartItemOrderProduct $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            OptionCartItemOrderProductTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = OptionCartItemOrderProductTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = OptionCartItemOrderProductTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var OptionCartItemOrderProduct $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                OptionCartItemOrderProductTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_ID);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_PRICE);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE);
            $criteria->addSelectColumn(OptionCartItemOrderProductTableMap::COL_QUANTITY);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.product_available_option_id');
            $criteria->addSelectColumn($alias . '.cart_item_option_id');
            $criteria->addSelectColumn($alias . '.order_product_id');
            $criteria->addSelectColumn($alias . '.option_order_product_id');
            $criteria->addSelectColumn($alias . '.customization_data');
            $criteria->addSelectColumn($alias . '.price');
            $criteria->addSelectColumn($alias . '.taxed_price');
            $criteria->addSelectColumn($alias . '.quantity');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_ID);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_PRICE);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE);
            $criteria->removeSelectColumn(OptionCartItemOrderProductTableMap::COL_QUANTITY);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.product_available_option_id');
            $criteria->removeSelectColumn($alias . '.cart_item_option_id');
            $criteria->removeSelectColumn($alias . '.order_product_id');
            $criteria->removeSelectColumn($alias . '.option_order_product_id');
            $criteria->removeSelectColumn($alias . '.customization_data');
            $criteria->removeSelectColumn($alias . '.price');
            $criteria->removeSelectColumn($alias . '.taxed_price');
            $criteria->removeSelectColumn($alias . '.quantity');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(OptionCartItemOrderProductTableMap::DATABASE_NAME)->getTable(OptionCartItemOrderProductTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a OptionCartItemOrderProduct or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or OptionCartItemOrderProduct object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Option\Model\OptionCartItemOrderProduct) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(OptionCartItemOrderProductTableMap::DATABASE_NAME);
            $criteria->add(OptionCartItemOrderProductTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = OptionCartItemOrderProductQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            OptionCartItemOrderProductTableMap::clearInstancePool();
        } elseif (!\is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                OptionCartItemOrderProductTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the option_cart_item_order_product table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return OptionCartItemOrderProductQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a OptionCartItemOrderProduct or Criteria object.
     *
     * @param mixed $criteria Criteria or OptionCartItemOrderProduct object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from OptionCartItemOrderProduct object
        }

        if ($criteria->containsKey(OptionCartItemOrderProductTableMap::COL_ID) && $criteria->keyContainsValue(OptionCartItemOrderProductTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.OptionCartItemOrderProductTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = OptionCartItemOrderProductQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
