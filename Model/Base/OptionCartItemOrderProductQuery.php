<?php

namespace Option\Model\Base;

use \Exception;
use \PDO;
use Option\Model\OptionCartItemOrderProduct as ChildOptionCartItemOrderProduct;
use Option\Model\OptionCartItemOrderProductQuery as ChildOptionCartItemOrderProductQuery;
use Option\Model\Map\OptionCartItemOrderProductTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\CartItem;
use Thelia\Model\OrderProduct;

/**
 * Base class that represents a query for the `option_cart_item_order_product` table.
 *
 * @method     ChildOptionCartItemOrderProductQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildOptionCartItemOrderProductQuery orderByProductAvailableOptionId($order = Criteria::ASC) Order by the product_available_option_id column
 * @method     ChildOptionCartItemOrderProductQuery orderByCartItemOptionId($order = Criteria::ASC) Order by the cart_item_option_id column
 * @method     ChildOptionCartItemOrderProductQuery orderByOrderProductId($order = Criteria::ASC) Order by the order_product_id column
 * @method     ChildOptionCartItemOrderProductQuery orderByOptionOrderProductId($order = Criteria::ASC) Order by the option_order_product_id column
 * @method     ChildOptionCartItemOrderProductQuery orderByCustomizationData($order = Criteria::ASC) Order by the customization_data column
 * @method     ChildOptionCartItemOrderProductQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method     ChildOptionCartItemOrderProductQuery orderByTaxedPrice($order = Criteria::ASC) Order by the taxed_price column
 * @method     ChildOptionCartItemOrderProductQuery orderByQuantity($order = Criteria::ASC) Order by the quantity column
 *
 * @method     ChildOptionCartItemOrderProductQuery groupById() Group by the id column
 * @method     ChildOptionCartItemOrderProductQuery groupByProductAvailableOptionId() Group by the product_available_option_id column
 * @method     ChildOptionCartItemOrderProductQuery groupByCartItemOptionId() Group by the cart_item_option_id column
 * @method     ChildOptionCartItemOrderProductQuery groupByOrderProductId() Group by the order_product_id column
 * @method     ChildOptionCartItemOrderProductQuery groupByOptionOrderProductId() Group by the option_order_product_id column
 * @method     ChildOptionCartItemOrderProductQuery groupByCustomizationData() Group by the customization_data column
 * @method     ChildOptionCartItemOrderProductQuery groupByPrice() Group by the price column
 * @method     ChildOptionCartItemOrderProductQuery groupByTaxedPrice() Group by the taxed_price column
 * @method     ChildOptionCartItemOrderProductQuery groupByQuantity() Group by the quantity column
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildOptionCartItemOrderProductQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildOptionCartItemOrderProductQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildOptionCartItemOrderProductQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildOptionCartItemOrderProductQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinCartItem($relationAlias = null) Adds a LEFT JOIN clause to the query using the CartItem relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinCartItem($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CartItem relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinCartItem($relationAlias = null) Adds a INNER JOIN clause to the query using the CartItem relation
 *
 * @method     ChildOptionCartItemOrderProductQuery joinWithCartItem($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the CartItem relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinWithCartItem() Adds a LEFT JOIN clause and with to the query using the CartItem relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinWithCartItem() Adds a RIGHT JOIN clause and with to the query using the CartItem relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinWithCartItem() Adds a INNER JOIN clause and with to the query using the CartItem relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinProductAvailableOption($relationAlias = null) Adds a LEFT JOIN clause to the query using the ProductAvailableOption relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinProductAvailableOption($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ProductAvailableOption relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinProductAvailableOption($relationAlias = null) Adds a INNER JOIN clause to the query using the ProductAvailableOption relation
 *
 * @method     ChildOptionCartItemOrderProductQuery joinWithProductAvailableOption($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ProductAvailableOption relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinWithProductAvailableOption() Adds a LEFT JOIN clause and with to the query using the ProductAvailableOption relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinWithProductAvailableOption() Adds a RIGHT JOIN clause and with to the query using the ProductAvailableOption relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinWithProductAvailableOption() Adds a INNER JOIN clause and with to the query using the ProductAvailableOption relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinOrderProductRelatedByOrderProductId($relationAlias = null) Adds a LEFT JOIN clause to the query using the OrderProductRelatedByOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinOrderProductRelatedByOrderProductId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OrderProductRelatedByOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinOrderProductRelatedByOrderProductId($relationAlias = null) Adds a INNER JOIN clause to the query using the OrderProductRelatedByOrderProductId relation
 *
 * @method     ChildOptionCartItemOrderProductQuery joinWithOrderProductRelatedByOrderProductId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the OrderProductRelatedByOrderProductId relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinWithOrderProductRelatedByOrderProductId() Adds a LEFT JOIN clause and with to the query using the OrderProductRelatedByOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinWithOrderProductRelatedByOrderProductId() Adds a RIGHT JOIN clause and with to the query using the OrderProductRelatedByOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinWithOrderProductRelatedByOrderProductId() Adds a INNER JOIN clause and with to the query using the OrderProductRelatedByOrderProductId relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinOrderProductRelatedByOptionOrderProductId($relationAlias = null) Adds a LEFT JOIN clause to the query using the OrderProductRelatedByOptionOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinOrderProductRelatedByOptionOrderProductId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OrderProductRelatedByOptionOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinOrderProductRelatedByOptionOrderProductId($relationAlias = null) Adds a INNER JOIN clause to the query using the OrderProductRelatedByOptionOrderProductId relation
 *
 * @method     ChildOptionCartItemOrderProductQuery joinWithOrderProductRelatedByOptionOrderProductId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the OrderProductRelatedByOptionOrderProductId relation
 *
 * @method     ChildOptionCartItemOrderProductQuery leftJoinWithOrderProductRelatedByOptionOrderProductId() Adds a LEFT JOIN clause and with to the query using the OrderProductRelatedByOptionOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery rightJoinWithOrderProductRelatedByOptionOrderProductId() Adds a RIGHT JOIN clause and with to the query using the OrderProductRelatedByOptionOrderProductId relation
 * @method     ChildOptionCartItemOrderProductQuery innerJoinWithOrderProductRelatedByOptionOrderProductId() Adds a INNER JOIN clause and with to the query using the OrderProductRelatedByOptionOrderProductId relation
 *
 * @method     \Thelia\Model\CartItemQuery|\Option\Model\ProductAvailableOptionQuery|\Thelia\Model\OrderProductQuery|\Thelia\Model\OrderProductQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildOptionCartItemOrderProduct|null findOne(?ConnectionInterface $con = null) Return the first ChildOptionCartItemOrderProduct matching the query
 * @method     ChildOptionCartItemOrderProduct findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildOptionCartItemOrderProduct matching the query, or a new ChildOptionCartItemOrderProduct object populated from the query conditions when no match is found
 *
 * @method     ChildOptionCartItemOrderProduct|null findOneById(int $id) Return the first ChildOptionCartItemOrderProduct filtered by the id column
 * @method     ChildOptionCartItemOrderProduct|null findOneByProductAvailableOptionId(int $product_available_option_id) Return the first ChildOptionCartItemOrderProduct filtered by the product_available_option_id column
 * @method     ChildOptionCartItemOrderProduct|null findOneByCartItemOptionId(int $cart_item_option_id) Return the first ChildOptionCartItemOrderProduct filtered by the cart_item_option_id column
 * @method     ChildOptionCartItemOrderProduct|null findOneByOrderProductId(int $order_product_id) Return the first ChildOptionCartItemOrderProduct filtered by the order_product_id column
 * @method     ChildOptionCartItemOrderProduct|null findOneByOptionOrderProductId(int $option_order_product_id) Return the first ChildOptionCartItemOrderProduct filtered by the option_order_product_id column
 * @method     ChildOptionCartItemOrderProduct|null findOneByCustomizationData(string $customization_data) Return the first ChildOptionCartItemOrderProduct filtered by the customization_data column
 * @method     ChildOptionCartItemOrderProduct|null findOneByPrice(string $price) Return the first ChildOptionCartItemOrderProduct filtered by the price column
 * @method     ChildOptionCartItemOrderProduct|null findOneByTaxedPrice(string $taxed_price) Return the first ChildOptionCartItemOrderProduct filtered by the taxed_price column
 * @method     ChildOptionCartItemOrderProduct|null findOneByQuantity(string $quantity) Return the first ChildOptionCartItemOrderProduct filtered by the quantity column
 *
 * @method     ChildOptionCartItemOrderProduct requirePk($key, ?ConnectionInterface $con = null) Return the ChildOptionCartItemOrderProduct by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOne(?ConnectionInterface $con = null) Return the first ChildOptionCartItemOrderProduct matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildOptionCartItemOrderProduct requireOneById(int $id) Return the first ChildOptionCartItemOrderProduct filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByProductAvailableOptionId(int $product_available_option_id) Return the first ChildOptionCartItemOrderProduct filtered by the product_available_option_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByCartItemOptionId(int $cart_item_option_id) Return the first ChildOptionCartItemOrderProduct filtered by the cart_item_option_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByOrderProductId(int $order_product_id) Return the first ChildOptionCartItemOrderProduct filtered by the order_product_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByOptionOrderProductId(int $option_order_product_id) Return the first ChildOptionCartItemOrderProduct filtered by the option_order_product_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByCustomizationData(string $customization_data) Return the first ChildOptionCartItemOrderProduct filtered by the customization_data column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByPrice(string $price) Return the first ChildOptionCartItemOrderProduct filtered by the price column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByTaxedPrice(string $taxed_price) Return the first ChildOptionCartItemOrderProduct filtered by the taxed_price column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildOptionCartItemOrderProduct requireOneByQuantity(string $quantity) Return the first ChildOptionCartItemOrderProduct filtered by the quantity column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildOptionCartItemOrderProduct[]|Collection find(?ConnectionInterface $con = null) Return ChildOptionCartItemOrderProduct objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> find(?ConnectionInterface $con = null) Return ChildOptionCartItemOrderProduct objects based on current ModelCriteria
 *
 * @method     ChildOptionCartItemOrderProduct[]|Collection findById(int|array<int> $id) Return ChildOptionCartItemOrderProduct objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findById(int|array<int> $id) Return ChildOptionCartItemOrderProduct objects filtered by the id column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByProductAvailableOptionId(int|array<int> $product_available_option_id) Return ChildOptionCartItemOrderProduct objects filtered by the product_available_option_id column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByProductAvailableOptionId(int|array<int> $product_available_option_id) Return ChildOptionCartItemOrderProduct objects filtered by the product_available_option_id column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByCartItemOptionId(int|array<int> $cart_item_option_id) Return ChildOptionCartItemOrderProduct objects filtered by the cart_item_option_id column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByCartItemOptionId(int|array<int> $cart_item_option_id) Return ChildOptionCartItemOrderProduct objects filtered by the cart_item_option_id column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByOrderProductId(int|array<int> $order_product_id) Return ChildOptionCartItemOrderProduct objects filtered by the order_product_id column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByOrderProductId(int|array<int> $order_product_id) Return ChildOptionCartItemOrderProduct objects filtered by the order_product_id column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByOptionOrderProductId(int|array<int> $option_order_product_id) Return ChildOptionCartItemOrderProduct objects filtered by the option_order_product_id column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByOptionOrderProductId(int|array<int> $option_order_product_id) Return ChildOptionCartItemOrderProduct objects filtered by the option_order_product_id column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByCustomizationData(string|array<string> $customization_data) Return ChildOptionCartItemOrderProduct objects filtered by the customization_data column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByCustomizationData(string|array<string> $customization_data) Return ChildOptionCartItemOrderProduct objects filtered by the customization_data column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByPrice(string|array<string> $price) Return ChildOptionCartItemOrderProduct objects filtered by the price column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByPrice(string|array<string> $price) Return ChildOptionCartItemOrderProduct objects filtered by the price column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByTaxedPrice(string|array<string> $taxed_price) Return ChildOptionCartItemOrderProduct objects filtered by the taxed_price column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByTaxedPrice(string|array<string> $taxed_price) Return ChildOptionCartItemOrderProduct objects filtered by the taxed_price column
 * @method     ChildOptionCartItemOrderProduct[]|Collection findByQuantity(string|array<string> $quantity) Return ChildOptionCartItemOrderProduct objects filtered by the quantity column
 * @psalm-method Collection&\Traversable<ChildOptionCartItemOrderProduct> findByQuantity(string|array<string> $quantity) Return ChildOptionCartItemOrderProduct objects filtered by the quantity column
 *
 * @method     ChildOptionCartItemOrderProduct[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildOptionCartItemOrderProduct> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class OptionCartItemOrderProductQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Option\Model\Base\OptionCartItemOrderProductQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'TheliaMain', $modelName = '\\Option\\Model\\OptionCartItemOrderProduct', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildOptionCartItemOrderProductQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildOptionCartItemOrderProductQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildOptionCartItemOrderProductQuery) {
            return $criteria;
        }
        $query = new ChildOptionCartItemOrderProductQuery();
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
     * @return ChildOptionCartItemOrderProduct|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = OptionCartItemOrderProductTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildOptionCartItemOrderProduct A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT `id`, `product_available_option_id`, `cart_item_option_id`, `order_product_id`, `option_order_product_id`, `customization_data`, `price`, `taxed_price`, `quantity` FROM `option_cart_item_order_product` WHERE `id` = :p0';
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
            /** @var ChildOptionCartItemOrderProduct $obj */
            $obj = new ChildOptionCartItemOrderProduct();
            $obj->hydrate($row);
            OptionCartItemOrderProductTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildOptionCartItemOrderProduct|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the product_available_option_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductAvailableOptionId(1234); // WHERE product_available_option_id = 1234
     * $query->filterByProductAvailableOptionId(array(12, 34)); // WHERE product_available_option_id IN (12, 34)
     * $query->filterByProductAvailableOptionId(array('min' => 12)); // WHERE product_available_option_id > 12
     * </code>
     *
     * @see       filterByProductAvailableOption()
     *
     * @param mixed $productAvailableOptionId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByProductAvailableOptionId($productAvailableOptionId = null, ?string $comparison = null)
    {
        if (is_array($productAvailableOptionId)) {
            $useMinMax = false;
            if (isset($productAvailableOptionId['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $productAvailableOptionId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productAvailableOptionId['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $productAvailableOptionId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $productAvailableOptionId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the cart_item_option_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCartItemOptionId(1234); // WHERE cart_item_option_id = 1234
     * $query->filterByCartItemOptionId(array(12, 34)); // WHERE cart_item_option_id IN (12, 34)
     * $query->filterByCartItemOptionId(array('min' => 12)); // WHERE cart_item_option_id > 12
     * </code>
     *
     * @see       filterByCartItem()
     *
     * @param mixed $cartItemOptionId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCartItemOptionId($cartItemOptionId = null, ?string $comparison = null)
    {
        if (is_array($cartItemOptionId)) {
            $useMinMax = false;
            if (isset($cartItemOptionId['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $cartItemOptionId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($cartItemOptionId['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $cartItemOptionId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $cartItemOptionId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the order_product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByOrderProductId(1234); // WHERE order_product_id = 1234
     * $query->filterByOrderProductId(array(12, 34)); // WHERE order_product_id IN (12, 34)
     * $query->filterByOrderProductId(array('min' => 12)); // WHERE order_product_id > 12
     * </code>
     *
     * @see       filterByOrderProductRelatedByOrderProductId()
     *
     * @param mixed $orderProductId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOrderProductId($orderProductId = null, ?string $comparison = null)
    {
        if (is_array($orderProductId)) {
            $useMinMax = false;
            if (isset($orderProductId['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $orderProductId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($orderProductId['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $orderProductId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $orderProductId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the option_order_product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByOptionOrderProductId(1234); // WHERE option_order_product_id = 1234
     * $query->filterByOptionOrderProductId(array(12, 34)); // WHERE option_order_product_id IN (12, 34)
     * $query->filterByOptionOrderProductId(array('min' => 12)); // WHERE option_order_product_id > 12
     * </code>
     *
     * @see       filterByOrderProductRelatedByOptionOrderProductId()
     *
     * @param mixed $optionOrderProductId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOptionOrderProductId($optionOrderProductId = null, ?string $comparison = null)
    {
        if (is_array($optionOrderProductId)) {
            $useMinMax = false;
            if (isset($optionOrderProductId['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $optionOrderProductId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($optionOrderProductId['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $optionOrderProductId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $optionOrderProductId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the customization_data column
     *
     * Example usage:
     * <code>
     * $query->filterByCustomizationData('fooValue');   // WHERE customization_data = 'fooValue'
     * $query->filterByCustomizationData('%fooValue%', Criteria::LIKE); // WHERE customization_data LIKE '%fooValue%'
     * $query->filterByCustomizationData(['foo', 'bar']); // WHERE customization_data IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $customizationData The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCustomizationData($customizationData = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($customizationData)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA, $customizationData, $comparison);

        return $this;
    }

    /**
     * Filter the query on the price column
     *
     * Example usage:
     * <code>
     * $query->filterByPrice(1234); // WHERE price = 1234
     * $query->filterByPrice(array(12, 34)); // WHERE price IN (12, 34)
     * $query->filterByPrice(array('min' => 12)); // WHERE price > 12
     * </code>
     *
     * @param mixed $price The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrice($price = null, ?string $comparison = null)
    {
        if (is_array($price)) {
            $useMinMax = false;
            if (isset($price['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRICE, $price, $comparison);

        return $this;
    }

    /**
     * Filter the query on the taxed_price column
     *
     * Example usage:
     * <code>
     * $query->filterByTaxedPrice(1234); // WHERE taxed_price = 1234
     * $query->filterByTaxedPrice(array(12, 34)); // WHERE taxed_price IN (12, 34)
     * $query->filterByTaxedPrice(array('min' => 12)); // WHERE taxed_price > 12
     * </code>
     *
     * @param mixed $taxedPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTaxedPrice($taxedPrice = null, ?string $comparison = null)
    {
        if (is_array($taxedPrice)) {
            $useMinMax = false;
            if (isset($taxedPrice['min'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE, $taxedPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($taxedPrice['max'])) {
                $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE, $taxedPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE, $taxedPrice, $comparison);

        return $this;
    }

    /**
     * Filter the query on the quantity column
     *
     * Example usage:
     * <code>
     * $query->filterByQuantity('fooValue');   // WHERE quantity = 'fooValue'
     * $query->filterByQuantity('%fooValue%', Criteria::LIKE); // WHERE quantity LIKE '%fooValue%'
     * $query->filterByQuantity(['foo', 'bar']); // WHERE quantity IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $quantity The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByQuantity($quantity = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($quantity)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_QUANTITY, $quantity, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Thelia\Model\CartItem object
     *
     * @param \Thelia\Model\CartItem|ObjectCollection $cartItem The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCartItem($cartItem, ?string $comparison = null)
    {
        if ($cartItem instanceof \Thelia\Model\CartItem) {
            return $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $cartItem->getId(), $comparison);
        } elseif ($cartItem instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $cartItem->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByCartItem() only accepts arguments of type \Thelia\Model\CartItem or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CartItem relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinCartItem(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CartItem');

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
            $this->addJoinObject($join, 'CartItem');
        }

        return $this;
    }

    /**
     * Use the CartItem relation CartItem object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Thelia\Model\CartItemQuery A secondary query class using the current class as primary query
     */
    public function useCartItemQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCartItem($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CartItem', '\Thelia\Model\CartItemQuery');
    }

    /**
     * Use the CartItem relation CartItem object
     *
     * @param callable(\Thelia\Model\CartItemQuery):\Thelia\Model\CartItemQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withCartItemQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useCartItemQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to CartItem table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Thelia\Model\CartItemQuery The inner query object of the EXISTS statement
     */
    public function useCartItemExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Thelia\Model\CartItemQuery */
        $q = $this->useExistsQuery('CartItem', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to CartItem table for a NOT EXISTS query.
     *
     * @see useCartItemExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\CartItemQuery The inner query object of the NOT EXISTS statement
     */
    public function useCartItemNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\CartItemQuery */
        $q = $this->useExistsQuery('CartItem', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to CartItem table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Thelia\Model\CartItemQuery The inner query object of the IN statement
     */
    public function useInCartItemQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Thelia\Model\CartItemQuery */
        $q = $this->useInQuery('CartItem', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to CartItem table for a NOT IN query.
     *
     * @see useCartItemInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\CartItemQuery The inner query object of the NOT IN statement
     */
    public function useNotInCartItemQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\CartItemQuery */
        $q = $this->useInQuery('CartItem', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Option\Model\ProductAvailableOption object
     *
     * @param \Option\Model\ProductAvailableOption|ObjectCollection $productAvailableOption The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByProductAvailableOption($productAvailableOption, ?string $comparison = null)
    {
        if ($productAvailableOption instanceof \Option\Model\ProductAvailableOption) {
            return $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $productAvailableOption->getId(), $comparison);
        } elseif ($productAvailableOption instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $productAvailableOption->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByProductAvailableOption() only accepts arguments of type \Option\Model\ProductAvailableOption or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ProductAvailableOption relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinProductAvailableOption(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ProductAvailableOption');

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
            $this->addJoinObject($join, 'ProductAvailableOption');
        }

        return $this;
    }

    /**
     * Use the ProductAvailableOption relation ProductAvailableOption object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Option\Model\ProductAvailableOptionQuery A secondary query class using the current class as primary query
     */
    public function useProductAvailableOptionQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinProductAvailableOption($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ProductAvailableOption', '\Option\Model\ProductAvailableOptionQuery');
    }

    /**
     * Use the ProductAvailableOption relation ProductAvailableOption object
     *
     * @param callable(\Option\Model\ProductAvailableOptionQuery):\Option\Model\ProductAvailableOptionQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withProductAvailableOptionQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useProductAvailableOptionQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ProductAvailableOption table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Option\Model\ProductAvailableOptionQuery The inner query object of the EXISTS statement
     */
    public function useProductAvailableOptionExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Option\Model\ProductAvailableOptionQuery */
        $q = $this->useExistsQuery('ProductAvailableOption', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ProductAvailableOption table for a NOT EXISTS query.
     *
     * @see useProductAvailableOptionExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\ProductAvailableOptionQuery The inner query object of the NOT EXISTS statement
     */
    public function useProductAvailableOptionNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\ProductAvailableOptionQuery */
        $q = $this->useExistsQuery('ProductAvailableOption', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ProductAvailableOption table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Option\Model\ProductAvailableOptionQuery The inner query object of the IN statement
     */
    public function useInProductAvailableOptionQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Option\Model\ProductAvailableOptionQuery */
        $q = $this->useInQuery('ProductAvailableOption', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ProductAvailableOption table for a NOT IN query.
     *
     * @see useProductAvailableOptionInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Option\Model\ProductAvailableOptionQuery The inner query object of the NOT IN statement
     */
    public function useNotInProductAvailableOptionQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Option\Model\ProductAvailableOptionQuery */
        $q = $this->useInQuery('ProductAvailableOption', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Thelia\Model\OrderProduct object
     *
     * @param \Thelia\Model\OrderProduct|ObjectCollection $orderProduct The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOrderProductRelatedByOrderProductId($orderProduct, ?string $comparison = null)
    {
        if ($orderProduct instanceof \Thelia\Model\OrderProduct) {
            return $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $orderProduct->getId(), $comparison);
        } elseif ($orderProduct instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $orderProduct->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByOrderProductRelatedByOrderProductId() only accepts arguments of type \Thelia\Model\OrderProduct or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OrderProductRelatedByOrderProductId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinOrderProductRelatedByOrderProductId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OrderProductRelatedByOrderProductId');

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
            $this->addJoinObject($join, 'OrderProductRelatedByOrderProductId');
        }

        return $this;
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation OrderProduct object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Thelia\Model\OrderProductQuery A secondary query class using the current class as primary query
     */
    public function useOrderProductRelatedByOrderProductIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOrderProductRelatedByOrderProductId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OrderProductRelatedByOrderProductId', '\Thelia\Model\OrderProductQuery');
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation OrderProduct object
     *
     * @param callable(\Thelia\Model\OrderProductQuery):\Thelia\Model\OrderProductQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withOrderProductRelatedByOrderProductIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useOrderProductRelatedByOrderProductIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation to the OrderProduct table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the EXISTS statement
     */
    public function useOrderProductRelatedByOrderProductIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useExistsQuery('OrderProductRelatedByOrderProductId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation to the OrderProduct table for a NOT EXISTS query.
     *
     * @see useOrderProductRelatedByOrderProductIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the NOT EXISTS statement
     */
    public function useOrderProductRelatedByOrderProductIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useExistsQuery('OrderProductRelatedByOrderProductId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation to the OrderProduct table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the IN statement
     */
    public function useInOrderProductRelatedByOrderProductIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useInQuery('OrderProductRelatedByOrderProductId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOrderProductId relation to the OrderProduct table for a NOT IN query.
     *
     * @see useOrderProductRelatedByOrderProductIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the NOT IN statement
     */
    public function useNotInOrderProductRelatedByOrderProductIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useInQuery('OrderProductRelatedByOrderProductId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Thelia\Model\OrderProduct object
     *
     * @param \Thelia\Model\OrderProduct|ObjectCollection $orderProduct The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByOrderProductRelatedByOptionOrderProductId($orderProduct, ?string $comparison = null)
    {
        if ($orderProduct instanceof \Thelia\Model\OrderProduct) {
            return $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $orderProduct->getId(), $comparison);
        } elseif ($orderProduct instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $orderProduct->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByOrderProductRelatedByOptionOrderProductId() only accepts arguments of type \Thelia\Model\OrderProduct or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OrderProductRelatedByOptionOrderProductId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinOrderProductRelatedByOptionOrderProductId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OrderProductRelatedByOptionOrderProductId');

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
            $this->addJoinObject($join, 'OrderProductRelatedByOptionOrderProductId');
        }

        return $this;
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation OrderProduct object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Thelia\Model\OrderProductQuery A secondary query class using the current class as primary query
     */
    public function useOrderProductRelatedByOptionOrderProductIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOrderProductRelatedByOptionOrderProductId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OrderProductRelatedByOptionOrderProductId', '\Thelia\Model\OrderProductQuery');
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation OrderProduct object
     *
     * @param callable(\Thelia\Model\OrderProductQuery):\Thelia\Model\OrderProductQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withOrderProductRelatedByOptionOrderProductIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useOrderProductRelatedByOptionOrderProductIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation to the OrderProduct table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the EXISTS statement
     */
    public function useOrderProductRelatedByOptionOrderProductIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useExistsQuery('OrderProductRelatedByOptionOrderProductId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation to the OrderProduct table for a NOT EXISTS query.
     *
     * @see useOrderProductRelatedByOptionOrderProductIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the NOT EXISTS statement
     */
    public function useOrderProductRelatedByOptionOrderProductIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useExistsQuery('OrderProductRelatedByOptionOrderProductId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation to the OrderProduct table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the IN statement
     */
    public function useInOrderProductRelatedByOptionOrderProductIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useInQuery('OrderProductRelatedByOptionOrderProductId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the OrderProductRelatedByOptionOrderProductId relation to the OrderProduct table for a NOT IN query.
     *
     * @see useOrderProductRelatedByOptionOrderProductIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Thelia\Model\OrderProductQuery The inner query object of the NOT IN statement
     */
    public function useNotInOrderProductRelatedByOptionOrderProductIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Thelia\Model\OrderProductQuery */
        $q = $this->useInQuery('OrderProductRelatedByOptionOrderProductId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildOptionCartItemOrderProduct $optionCartItemOrderProduct Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($optionCartItemOrderProduct = null)
    {
        if ($optionCartItemOrderProduct) {
            $this->addUsingAlias(OptionCartItemOrderProductTableMap::COL_ID, $optionCartItemOrderProduct->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the option_cart_item_order_product table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            OptionCartItemOrderProductTableMap::clearInstancePool();
            OptionCartItemOrderProductTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(OptionCartItemOrderProductTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            OptionCartItemOrderProductTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            OptionCartItemOrderProductTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
