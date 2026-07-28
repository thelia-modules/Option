<?php

namespace Option\Model\Base;

use \Exception;
use \PDO;
use Option\Model\ProductAvailableOption as ChildProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery as ChildProductAvailableOptionQuery;
use Option\Model\Map\ProductAvailableOptionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Product;

/**
 * Base class that represents a query for the `product_available_option` table.
 *
 * @method     ChildProductAvailableOptionQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildProductAvailableOptionQuery orderByProductId($order = Criteria::ASC) Order by the product_id column
 * @method     ChildProductAvailableOptionQuery orderByOptionId($order = Criteria::ASC) Order by the option_id column
 * @method     ChildProductAvailableOptionQuery orderByOptionAddedBy($order = Criteria::ASC) Order by the option_added_by column
 *
 * @method     ChildProductAvailableOptionQuery groupById() Group by the id column
 * @method     ChildProductAvailableOptionQuery groupByProductId() Group by the product_id column
 * @method     ChildProductAvailableOptionQuery groupByOptionId() Group by the option_id column
 * @method     ChildProductAvailableOptionQuery groupByOptionAddedBy() Group by the option_added_by column
 *
 * @method     ChildProductAvailableOptionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildProductAvailableOptionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildProductAvailableOptionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildProductAvailableOptionQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildProductAvailableOptionQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildProductAvailableOptionQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildProductAvailableOptionQuery leftJoinProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the Product relation
 * @method     ChildProductAvailableOptionQuery rightJoinProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Product relation
 * @method     ChildProductAvailableOptionQuery innerJoinProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the Product relation
 *
 * @method     ChildProductAvailableOptionQuery joinWithProduct($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Product relation
 *
 * @method     ChildProductAvailableOptionQuery leftJoinWithProduct() Adds a LEFT JOIN clause and with to the query using the Product relation
 * @method     ChildProductAvailableOptionQuery rightJoinWithProduct() Adds a RIGHT JOIN clause and with to the query using the Product relation
 * @method     ChildProductAvailableOptionQuery innerJoinWithProduct() Adds a INNER JOIN clause and with to the query using the Product relation
 *
 * @method     ChildProductAvailableOptionQuery leftJoinOptionProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the OptionProduct relation
 * @method     ChildProductAvailableOptionQuery rightJoinOptionProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OptionProduct relation
 * @method     ChildProductAvailableOptionQuery innerJoinOptionProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the OptionProduct relation
 *
 * @method     ChildProductAvailableOptionQuery joinWithOptionProduct($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the OptionProduct relation
 *
 * @method     ChildProductAvailableOptionQuery leftJoinWithOptionProduct() Adds a LEFT JOIN clause and with to the query using the OptionProduct relation
 * @method     ChildProductAvailableOptionQuery rightJoinWithOptionProduct() Adds a RIGHT JOIN clause and with to the query using the OptionProduct relation
 * @method     ChildProductAvailableOptionQuery innerJoinWithOptionProduct() Adds a INNER JOIN clause and with to the query using the OptionProduct relation
 *
 * @method     ChildProductAvailableOptionQuery leftJoinOptionCartItemOrderProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the OptionCartItemOrderProduct relation
 * @method     ChildProductAvailableOptionQuery rightJoinOptionCartItemOrderProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OptionCartItemOrderProduct relation
 * @method     ChildProductAvailableOptionQuery innerJoinOptionCartItemOrderProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the OptionCartItemOrderProduct relation
 *
 * @method     ChildProductAvailableOptionQuery joinWithOptionCartItemOrderProduct($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the OptionCartItemOrderProduct relation
 *
 * @method     ChildProductAvailableOptionQuery leftJoinWithOptionCartItemOrderProduct() Adds a LEFT JOIN clause and with to the query using the OptionCartItemOrderProduct relation
 * @method     ChildProductAvailableOptionQuery rightJoinWithOptionCartItemOrderProduct() Adds a RIGHT JOIN clause and with to the query using the OptionCartItemOrderProduct relation
 * @method     ChildProductAvailableOptionQuery innerJoinWithOptionCartItemOrderProduct() Adds a INNER JOIN clause and with to the query using the OptionCartItemOrderProduct relation
 *
 * @method     \Thelia\Model\ProductQuery|\Option\Model\OptionProductQuery|\Option\Model\OptionCartItemOrderProductQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildProductAvailableOption|null findOne(?ConnectionInterface $con = null) Return the first ChildProductAvailableOption matching the query
 * @method     ChildProductAvailableOption findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildProductAvailableOption matching the query, or a new ChildProductAvailableOption object populated from the query conditions when no match is found
 *
 * @method     ChildProductAvailableOption|null findOneById(int $id) Return the first ChildProductAvailableOption filtered by the id column
 * @method     ChildProductAvailableOption|null findOneByProductId(int $product_id) Return the first ChildProductAvailableOption filtered by the product_id column
 * @method     ChildProductAvailableOption|null findOneByOptionId(int $option_id) Return the first ChildProductAvailableOption filtered by the option_id column
 * @method     ChildProductAvailableOption|null findOneByOptionAddedBy(string $option_added_by) Return the first ChildProductAvailableOption filtered by the option_added_by column
 *
 * @method     ChildProductAvailableOption requirePk($key, ?ConnectionInterface $con = null) Return the ChildProductAvailableOption by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProductAvailableOption requireOne(?ConnectionInterface $con = null) Return the first ChildProductAvailableOption matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildProductAvailableOption requireOneById(int $id) Return the first ChildProductAvailableOption filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProductAvailableOption requireOneByProductId(int $product_id) Return the first ChildProductAvailableOption filtered by the product_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProductAvailableOption requireOneByOptionId(int $option_id) Return the first ChildProductAvailableOption filtered by the option_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildProductAvailableOption requireOneByOptionAddedBy(string $option_added_by) Return the first ChildProductAvailableOption filtered by the option_added_by column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildProductAvailableOption[]|Collection find(?ConnectionInterface $con = null) Return ChildProductAvailableOption objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildProductAvailableOption> find(?ConnectionInterface $con = null) Return ChildProductAvailableOption objects based on current ModelCriteria
 *
 * @method     ChildProductAvailableOption[]|Collection findById(int|array<int> $id) Return ChildProductAvailableOption objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildProductAvailableOption> findById(int|array<int> $id) Return ChildProductAvailableOption objects filtered by the id column
 * @method     ChildProductAvailableOption[]|Collection findByProductId(int|array<int> $product_id) Return ChildProductAvailableOption objects filtered by the product_id column
 * @psalm-method Collection&\Traversable<ChildProductAvailableOption> findByProductId(int|array<int> $product_id) Return ChildProductAvailableOption objects filtered by the product_id column
 * @method     ChildProductAvailableOption[]|Collection findByOptionId(int|array<int> $option_id) Return ChildProductAvailableOption objects filtered by the option_id column
 * @psalm-method Collection&\Traversable<ChildProductAvailableOption> findByOptionId(int|array<int> $option_id) Return ChildProductAvailableOption objects filtered by the option_id column
 * @method     ChildProductAvailableOption[]|Collection findByOptionAddedBy(string|array<string> $option_added_by) Return ChildProductAvailableOption objects filtered by the option_added_by column
 * @psalm-method Collection&\Traversable<ChildProductAvailableOption> findByOptionAddedBy(string|array<string> $option_added_by) Return ChildProductAvailableOption objects filtered by the option_added_by column
 *
 * @method     ChildProductAvailableOption[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildProductAvailableOption> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ProductAvailableOptionQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Option\Model\Base\ProductAvailableOptionQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'TheliaMain', $modelName = '\\Option\\Model\\ProductAvailableOption', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildProductAvailableOptionQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildProductAvailableOptionQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildProductAvailableOptionQuery) {
            return $criteria;
        }
        $query = new ChildProductAvailableOptionQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildProductAvailableOption|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProductAvailableOptionTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ProductAvailableOptionTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildProductAvailableOption A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT `id`, `product_id`, `option_id`, `option_added_by` FROM `product_available_option` WHERE `id` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildProductAvailableOption $obj */
            $obj = new ChildProductAvailableOption();
            $obj->hydrate($row);
            ProductAvailableOptionTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildProductAvailableOption|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $key, Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductId(1234); // WHERE product_id = 1234
     * $query->filterByProductId(array(12, 34)); // WHERE product_id IN (12, 34)
     * $query->filterByProductId(array('min' => 12)); // WHERE product_id > 12
     * </code>
     *
     * @see       filterByProduct()
     *
     * @param mixed $productId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByProductId($productId = null, ?string $comparison = null)
    {
        if (is_array($productId)) {
            $useMinMax = false;
            if (isset($productId['min'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_PRODUCT_ID, $productId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productId['max'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_PRODUCT_ID, $productId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_PRODUCT_ID, $productId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the option_id column
     *
     * Example usage:
     * <code>
     * $query->filterByOptionId(1234); // WHERE option_id = 1234
     * $query->filterByOptionId(array(12, 34)); // WHERE option_id IN (12, 34)
     * $query->filterByOptionId(array('min' => 12)); // WHERE option_id > 12
     * </code>
     *
     * @see       filterByOptionProduct()
     *
     * @param mixed $optionId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOptionId($optionId = null, ?string $comparison = null)
    {
        if (is_array($optionId)) {
            $useMinMax = false;
            if (isset($optionId['min'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ID, $optionId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($optionId['max'])) {
                $this->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ID, $optionId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ID, $optionId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the option_added_by column
     *
     * Example usage:
     * <code>
     * $query->filterByOptionAddedBy('fooValue');   // WHERE option_added_by = 'fooValue'
     * $query->filterByOptionAddedBy('%fooValue%', Criteria::LIKE); // WHERE option_added_by LIKE '%fooValue%'
     * $query->filterByOptionAddedBy(['foo', 'bar']); // WHERE option_added_by IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $optionAddedBy The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOptionAddedBy($optionAddedBy = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($optionAddedBy)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ADDED_BY, $optionAddedBy, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Thelia\Model\Product object
     *
     * @param \Thelia\Model\Product|ObjectCollection $product The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByProduct($product, ?string $comparison = null)
    {
        if ($product instanceof \Thelia\Model\Product) {
            return $this
                ->addUsingAlias(ProductAvailableOptionTableMap::COL_PRODUCT_ID, $product->getId(), $comparison);
        } elseif ($product instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ProductAvailableOptionTableMap::COL_PRODUCT_ID, $product->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByProduct() only accepts arguments of type \Thelia\Model\Product or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Product relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinProduct(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Product');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Product');
        }

        return $this;
    }

    /**
     * Use the Product relation Product object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Thelia\Model\ProductQuery A secondary query class using the current class as primary query
     */
    public function useProductQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Product', '\Thelia\Model\ProductQuery');
    }

    /**
     * Use the Product relation Product object
     *
     * @param callable(\Thelia\Model\ProductQuery):\Thelia\Model\ProductQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withProductQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useProductQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Product table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Thelia\Model\ProductQuery The inner query object of the EXISTS statement
     */
    public function useProductExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Thelia\Model\ProductQuery */
        $q = $this->useExistsQuery('Product', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Product table for a NOT EXISTS query.
     *
     * @see useProductExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\ProductQuery The inner query object of the NOT EXISTS statement
     */
    public function useProductNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\ProductQuery */
        $q = $this->useExistsQuery('Product', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Product table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Thelia\Model\ProductQuery The inner query object of the IN statement
     */
    public function useInProductQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Thelia\Model\ProductQuery */
        $q = $this->useInQuery('Product', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Product table for a NOT IN query.
     *
     * @see useProductInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\ProductQuery The inner query object of the NOT IN statement
     */
    public function useNotInProductQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\ProductQuery */
        $q = $this->useInQuery('Product', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Option\Model\OptionProduct object
     *
     * @param \Option\Model\OptionProduct|ObjectCollection $optionProduct The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOptionProduct($optionProduct, ?string $comparison = null)
    {
        if ($optionProduct instanceof \Option\Model\OptionProduct) {
            return $this
                ->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ID, $optionProduct->getId(), $comparison);
        } elseif ($optionProduct instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ProductAvailableOptionTableMap::COL_OPTION_ID, $optionProduct->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByOptionProduct() only accepts arguments of type \Option\Model\OptionProduct or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OptionProduct relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinOptionProduct(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OptionProduct');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OptionProduct');
        }

        return $this;
    }

    /**
     * Use the OptionProduct relation OptionProduct object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Option\Model\OptionProductQuery A secondary query class using the current class as primary query
     */
    public function useOptionProductQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinOptionProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OptionProduct', '\Option\Model\OptionProductQuery');
    }

    /**
     * Use the OptionProduct relation OptionProduct object
     *
     * @param callable(\Option\Model\OptionProductQuery):\Option\Model\OptionProductQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withOptionProductQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useOptionProductQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to OptionProduct table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Option\Model\OptionProductQuery The inner query object of the EXISTS statement
     */
    public function useOptionProductExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Option\Model\OptionProductQuery */
        $q = $this->useExistsQuery('OptionProduct', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to OptionProduct table for a NOT EXISTS query.
     *
     * @see useOptionProductExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\OptionProductQuery The inner query object of the NOT EXISTS statement
     */
    public function useOptionProductNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\OptionProductQuery */
        $q = $this->useExistsQuery('OptionProduct', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to OptionProduct table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Option\Model\OptionProductQuery The inner query object of the IN statement
     */
    public function useInOptionProductQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Option\Model\OptionProductQuery */
        $q = $this->useInQuery('OptionProduct', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to OptionProduct table for a NOT IN query.
     *
     * @see useOptionProductInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\OptionProductQuery The inner query object of the NOT IN statement
     */
    public function useNotInOptionProductQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\OptionProductQuery */
        $q = $this->useInQuery('OptionProduct', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Option\Model\OptionCartItemOrderProduct object
     *
     * @param \Option\Model\OptionCartItemOrderProduct|ObjectCollection $optionCartItemOrderProduct the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOptionCartItemOrderProduct($optionCartItemOrderProduct, ?string $comparison = null)
    {
        if ($optionCartItemOrderProduct instanceof \Option\Model\OptionCartItemOrderProduct) {
            $this
                ->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $optionCartItemOrderProduct->getProductAvailableOptionId(), $comparison);

            return $this;
        } elseif ($optionCartItemOrderProduct instanceof ObjectCollection) {
            $this
                ->useOptionCartItemOrderProductQuery()
                ->filterByPrimaryKeys($optionCartItemOrderProduct->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByOptionCartItemOrderProduct() only accepts arguments of type \Option\Model\OptionCartItemOrderProduct or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OptionCartItemOrderProduct relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinOptionCartItemOrderProduct(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OptionCartItemOrderProduct');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OptionCartItemOrderProduct');
        }

        return $this;
    }

    /**
     * Use the OptionCartItemOrderProduct relation OptionCartItemOrderProduct object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Option\Model\OptionCartItemOrderProductQuery A secondary query class using the current class as primary query
     */
    public function useOptionCartItemOrderProductQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOptionCartItemOrderProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OptionCartItemOrderProduct', '\Option\Model\OptionCartItemOrderProductQuery');
    }

    /**
     * Use the OptionCartItemOrderProduct relation OptionCartItemOrderProduct object
     *
     * @param callable(\Option\Model\OptionCartItemOrderProductQuery):\Option\Model\OptionCartItemOrderProductQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withOptionCartItemOrderProductQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useOptionCartItemOrderProductQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to OptionCartItemOrderProduct table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Option\Model\OptionCartItemOrderProductQuery The inner query object of the EXISTS statement
     */
    public function useOptionCartItemOrderProductExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Option\Model\OptionCartItemOrderProductQuery */
        $q = $this->useExistsQuery('OptionCartItemOrderProduct', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to OptionCartItemOrderProduct table for a NOT EXISTS query.
     *
     * @see useOptionCartItemOrderProductExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\OptionCartItemOrderProductQuery The inner query object of the NOT EXISTS statement
     */
    public function useOptionCartItemOrderProductNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\OptionCartItemOrderProductQuery */
        $q = $this->useExistsQuery('OptionCartItemOrderProduct', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to OptionCartItemOrderProduct table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Option\Model\OptionCartItemOrderProductQuery The inner query object of the IN statement
     */
    public function useInOptionCartItemOrderProductQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Option\Model\OptionCartItemOrderProductQuery */
        $q = $this->useInQuery('OptionCartItemOrderProduct', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to OptionCartItemOrderProduct table for a NOT IN query.
     *
     * @see useOptionCartItemOrderProductInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\OptionCartItemOrderProductQuery The inner query object of the NOT IN statement
     */
    public function useNotInOptionCartItemOrderProductQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\OptionCartItemOrderProductQuery */
        $q = $this->useInQuery('OptionCartItemOrderProduct', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildProductAvailableOption $productAvailableOption Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($productAvailableOption = null)
    {
        if ($productAvailableOption) {
            $this->addUsingAlias(ProductAvailableOptionTableMap::COL_ID, $productAvailableOption->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the product_available_option table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductAvailableOptionTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ProductAvailableOptionTableMap::clearInstancePool();
            ProductAvailableOptionTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductAvailableOptionTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ProductAvailableOptionTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ProductAvailableOptionTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ProductAvailableOptionTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
