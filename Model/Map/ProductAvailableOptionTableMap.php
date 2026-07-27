<?php

namespace Option\Model\Map;

use Option\Model\ProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery;
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
 * This class defines the structure of the 'product_available_option' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ProductAvailableOptionTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Option.Model.Map.ProductAvailableOptionTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'TheliaMain';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'product_available_option';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ProductAvailableOption';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Option\\Model\\ProductAvailableOption';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Option.Model.ProductAvailableOption';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 4;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 4;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'product_available_option.id';

    /**
     * the column name for the product_id field
     */
    public const COL_PRODUCT_ID = 'product_available_option.product_id';

    /**
     * the column name for the option_id field
     */
    public const COL_OPTION_ID = 'product_available_option.option_id';

    /**
     * the column name for the option_added_by field
     */
    public const COL_OPTION_ADDED_BY = 'product_available_option.option_added_by';

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
        self::TYPE_PHPNAME       => ['Id', 'ProductId', 'OptionId', 'OptionAddedBy', ],
        self::TYPE_CAMELNAME     => ['id', 'productId', 'optionId', 'optionAddedBy', ],
        self::TYPE_COLNAME       => [ProductAvailableOptionTableMap::COL_ID, ProductAvailableOptionTableMap::COL_PRODUCT_ID, ProductAvailableOptionTableMap::COL_OPTION_ID, ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY, ],
        self::TYPE_FIELDNAME     => ['id', 'product_id', 'option_id', 'option_added_by', ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'ProductId' => 1, 'OptionId' => 2, 'OptionAddedBy' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'productId' => 1, 'optionId' => 2, 'optionAddedBy' => 3, ],
        self::TYPE_COLNAME       => [ProductAvailableOptionTableMap::COL_ID => 0, ProductAvailableOptionTableMap::COL_PRODUCT_ID => 1, ProductAvailableOptionTableMap::COL_OPTION_ID => 2, ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY => 3, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'product_id' => 1, 'option_id' => 2, 'option_added_by' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected $normalizedColumnNameMap = [
        'Id' => 'ID',
        'ProductAvailableOption.Id' => 'ID',
        'id' => 'ID',
        'productAvailableOption.id' => 'ID',
        'ProductAvailableOptionTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'product_available_option.id' => 'ID',
        'ProductId' => 'PRODUCT_ID',
        'ProductAvailableOption.ProductId' => 'PRODUCT_ID',
        'productId' => 'PRODUCT_ID',
        'productAvailableOption.productId' => 'PRODUCT_ID',
        'ProductAvailableOptionTableMap::COL_PRODUCT_ID' => 'PRODUCT_ID',
        'COL_PRODUCT_ID' => 'PRODUCT_ID',
        'product_id' => 'PRODUCT_ID',
        'product_available_option.product_id' => 'PRODUCT_ID',
        'OptionId' => 'OPTION_ID',
        'ProductAvailableOption.OptionId' => 'OPTION_ID',
        'optionId' => 'OPTION_ID',
        'productAvailableOption.optionId' => 'OPTION_ID',
        'ProductAvailableOptionTableMap::COL_OPTION_ID' => 'OPTION_ID',
        'COL_OPTION_ID' => 'OPTION_ID',
        'option_id' => 'OPTION_ID',
        'product_available_option.option_id' => 'OPTION_ID',
        'OptionAddedBy' => 'OPTION_ADDED_BY',
        'ProductAvailableOption.OptionAddedBy' => 'OPTION_ADDED_BY',
        'optionAddedBy' => 'OPTION_ADDED_BY',
        'productAvailableOption.optionAddedBy' => 'OPTION_ADDED_BY',
        'ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY' => 'OPTION_ADDED_BY',
        'COL_OPTION_ADDED_BY' => 'OPTION_ADDED_BY',
        'option_added_by' => 'OPTION_ADDED_BY',
        'product_available_option.option_added_by' => 'OPTION_ADDED_BY',
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
        $this->setName('product_available_option');
        $this->setPhpName('ProductAvailableOption');
        $this->setIdentifierQuoting(true);
        $this->setClassName('\\Option\\Model\\ProductAvailableOption');
        $this->setPackage('Option.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('product_id', 'ProductId', 'INTEGER', 'product', 'id', true, null, null);
        $this->addForeignKey('option_id', 'OptionId', 'INTEGER', 'option_product', 'id', true, null, null);
        $this->addColumn('option_added_by', 'OptionAddedBy', 'JSON', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('Product', '\\Thelia\\Model\\Product', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':product_id',
    1 => ':id',
  ),
), 'CASCADE', 'RESTRICT', null, false);
        $this->addRelation('OptionProduct', '\\Option\\Model\\OptionProduct', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':option_id',
    1 => ':id',
  ),
), 'CASCADE', 'RESTRICT', null, false);
        $this->addRelation('OptionCartItemOrderProduct', '\\Option\\Model\\OptionCartItemOrderProduct', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':product_available_option_id',
    1 => ':id',
  ),
), 'SET NULL', null, 'OptionCartItemOrderProducts', false);
    }

    /**
     * Method to invalidate the instance pool of all tables related to product_available_option     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        OptionCartItemOrderProductTableMap::clearInstancePool();
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
        return $withPrefix ? ProductAvailableOptionTableMap::CLASS_DEFAULT : ProductAvailableOptionTableMap::OM_CLASS;
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
     * @return array (ProductAvailableOption object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ProductAvailableOptionTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ProductAvailableOptionTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ProductAvailableOptionTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ProductAvailableOptionTableMap::OM_CLASS;
            /** @var ProductAvailableOption $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ProductAvailableOptionTableMap::addInstanceToPool($obj, $key);
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
            $key = ProductAvailableOptionTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ProductAvailableOptionTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ProductAvailableOption $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ProductAvailableOptionTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ProductAvailableOptionTableMap::COL_ID);
            $criteria->addSelectColumn(ProductAvailableOptionTableMap::COL_PRODUCT_ID);
            $criteria->addSelectColumn(ProductAvailableOptionTableMap::COL_OPTION_ID);
            $criteria->addSelectColumn(ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.product_id');
            $criteria->addSelectColumn($alias . '.option_id');
            $criteria->addSelectColumn($alias . '.option_added_by');
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
            $criteria->removeSelectColumn(ProductAvailableOptionTableMap::COL_ID);
            $criteria->removeSelectColumn(ProductAvailableOptionTableMap::COL_PRODUCT_ID);
            $criteria->removeSelectColumn(ProductAvailableOptionTableMap::COL_OPTION_ID);
            $criteria->removeSelectColumn(ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.product_id');
            $criteria->removeSelectColumn($alias . '.option_id');
            $criteria->removeSelectColumn($alias . '.option_added_by');
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
        return Propel::getServiceContainer()->getDatabaseMap(ProductAvailableOptionTableMap::DATABASE_NAME)->getTable(ProductAvailableOptionTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ProductAvailableOption or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ProductAvailableOption object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ProductAvailableOptionTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Option\Model\ProductAvailableOption) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ProductAvailableOptionTableMap::DATABASE_NAME);
            $criteria->add(ProductAvailableOptionTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ProductAvailableOptionQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ProductAvailableOptionTableMap::clearInstancePool();
        } elseif (!\is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ProductAvailableOptionTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the product_available_option table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ProductAvailableOptionQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ProductAvailableOption or Criteria object.
     *
     * @param mixed $criteria Criteria or ProductAvailableOption object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductAvailableOptionTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ProductAvailableOption object
        }

        if ($criteria->containsKey(ProductAvailableOptionTableMap::COL_ID) && $criteria->keyContainsValue(ProductAvailableOptionTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ProductAvailableOptionTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ProductAvailableOptionQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
