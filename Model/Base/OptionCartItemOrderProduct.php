<?php

namespace Option\Model\Base;

use \Exception;
use \PDO;
use Option\Model\OptionCartItemOrderProductQuery as ChildOptionCartItemOrderProductQuery;
use Option\Model\ProductAvailableOption as ChildProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery as ChildProductAvailableOptionQuery;
use Option\Model\Event\OptionCartItemOrderProductEvent;
use Option\Model\Map\OptionCartItemOrderProductTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Thelia\Model\CartItem;
use Thelia\Model\CartItemQuery;
use Thelia\Model\OrderProduct;
use Thelia\Model\OrderProductQuery;

/**
 * Base class that represents a row from the 'option_cart_item_order_product' table.
 *
 *
 *
 * @package    propel.generator.Option.Model.Base
 */
abstract class OptionCartItemOrderProduct implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Option\\Model\\Map\\OptionCartItemOrderProductTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var bool
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var bool
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = [];

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = [];

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected ?int $id = null;

    /**
     * The value for the product_available_option_id field.
     *
     * @var        int|null
     */
    protected ?int $product_available_option_id = null;

    /**
     * The value for the cart_item_option_id field.
     *
     * @var        int|null
     */
    protected ?int $cart_item_option_id = null;

    /**
     * The value for the order_product_id field.
     *
     * @var        int|null
     */
    protected ?int $order_product_id = null;

    /**
     * The value for the option_order_product_id field.
     *
     * @var        int|null
     */
    protected ?int $option_order_product_id = null;

    /**
     * The value for the customization_data field.
     *
     * @var        string|null
     */
    protected ?string $customization_data = null;

    /**
     * The value for the price field.
     *
     * Note: this column has a database default value of: '0.000000'
     * @var        string|null
     */
    protected ?string $price = null;

    /**
     * The value for the taxed_price field.
     *
     * Note: this column has a database default value of: '0.000000'
     * @var        string|null
     */
    protected ?string $taxed_price = null;

    /**
     * The value for the quantity field.
     *
     * @var        string|null
     */
    protected ?string $quantity = null;

    /**
     * @var        CartItem
     */
    protected $aCartItem;

    /**
     * @var        ChildProductAvailableOption
     */
    protected $aProductAvailableOption;

    /**
     * @var        OrderProduct
     */
    protected $aOrderProductRelatedByOrderProductId;

    /**
     * @var        OrderProduct
     */
    protected $aOrderProductRelatedByOptionOrderProductId;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues(): void
    {
        $this->price = '0.000000';
        $this->taxed_price = '0.000000';
    }

    /**
     * Initializes internal state of Option\Model\Base\OptionCartItemOrderProduct object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return bool True if the object has been modified.
     */
    public function isModified(): bool
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param string $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return bool True if $col has been modified.
     */
    public function isColumnModified(string $col): bool
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns(): array
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return bool True, if the object has never been persisted.
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param bool $b the state of the object.
     */
    public function setNew(bool $b): void
    {
        $this->new = $b;
    }

    /**
     * Whether this object has been deleted.
     * @return bool The deleted state of this object.
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param bool $b The deleted state of this object.
     * @return void
     */
    public function setDeleted(bool $b): void
    {
        $this->deleted = $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified(?string $col = null): void
    {
        if (null !== $col) {
            unset($this->modifiedColumns[$col]);
        } else {
            $this->modifiedColumns = [];
        }
    }

    /**
     * Compares this with another <code>OptionCartItemOrderProduct</code> instance.  If
     * <code>obj</code> is an instance of <code>OptionCartItemOrderProduct</code>, delegates to
     * <code>equals(OptionCartItemOrderProduct)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param mixed $obj The object to compare to.
     * @return bool Whether equal to the object specified.
     */
    public function equals($obj): bool
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array<string, mixed>
     */
    public function getVirtualColumns(): array
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @return bool
     */
    public function hasVirtualColumn(string $name): bool
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     *
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getVirtualColumn(string $name): mixed
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of nonexistent virtual column `%s`.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @param mixed $value The value to give to the virtual column
     *
     * @return $this The current object, for fluid interface
     */
    public function setVirtualColumn(string $name, mixed $value): static
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param string $msg
     * @param int $priority One of the Propel::LOG_* logging levels
     * @return void
     */
    protected function log(string $msg, int $priority = Propel::LOG_INFO): void
    {
        Propel::log(\get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param \Propel\Runtime\Parser\AbstractParser|string $parser An AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME, TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM. Defaults to TableMap::TYPE_PHPNAME.
     * @return string The exported data
     */
    public function exportTo($parser, bool $includeLazyLoadColumns = true, string $keyType = TableMap::TYPE_PHPNAME): string
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray($keyType, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     *
     * @return array<string>
     */
    public function __sleep(): array
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the [product_available_option_id] column value.
     *
     * @return int|null
     */
    public function getProductAvailableOptionId(): ?int
    {
        return $this->product_available_option_id;
    }

    /**
     * Get the [cart_item_option_id] column value.
     *
     * @return int|null
     */
    public function getCartItemOptionId(): ?int
    {
        return $this->cart_item_option_id;
    }

    /**
     * Get the [order_product_id] column value.
     *
     * @return int|null
     */
    public function getOrderProductId(): ?int
    {
        return $this->order_product_id;
    }

    /**
     * Get the [option_order_product_id] column value.
     *
     * @return int|null
     */
    public function getOptionOrderProductId(): ?int
    {
        return $this->option_order_product_id;
    }

    /**
     * Get the [customization_data] column value.
     *
     * @return string|null
     */
    public function getCustomizationData(): ?string
    {
        return $this->customization_data;
    }

    /**
     * Get the [price] column value.
     *
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * Get the [taxed_price] column value.
     *
     * @return string|null
     */
    public function getTaxedPrice(): ?string
    {
        return $this->taxed_price;
    }

    /**
     * Get the [quantity] column value.
     *
     * @return string|null
     */
    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [product_available_option_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setProductAvailableOptionId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->product_available_option_id !== $v) {
            $this->product_available_option_id = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID] = true;
        }

        if ($this->aProductAvailableOption !== null && $this->aProductAvailableOption->getId() !== $v) {
            $this->aProductAvailableOption = null;
        }

        return $this;
    }

    /**
     * Set the value of [cart_item_option_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setCartItemOptionId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->cart_item_option_id !== $v) {
            $this->cart_item_option_id = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID] = true;
        }

        if ($this->aCartItem !== null && $this->aCartItem->getId() !== $v) {
            $this->aCartItem = null;
        }

        return $this;
    }

    /**
     * Set the value of [order_product_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setOrderProductId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->order_product_id !== $v) {
            $this->order_product_id = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID] = true;
        }

        if ($this->aOrderProductRelatedByOrderProductId !== null && $this->aOrderProductRelatedByOrderProductId->getId() !== $v) {
            $this->aOrderProductRelatedByOrderProductId = null;
        }

        return $this;
    }

    /**
     * Set the value of [option_order_product_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setOptionOrderProductId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->option_order_product_id !== $v) {
            $this->option_order_product_id = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID] = true;
        }

        if ($this->aOrderProductRelatedByOptionOrderProductId !== null && $this->aOrderProductRelatedByOptionOrderProductId->getId() !== $v) {
            $this->aOrderProductRelatedByOptionOrderProductId = null;
        }

        return $this;
    }

    /**
     * Set the value of [customization_data] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setCustomizationData(?string $v = null): static
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->customization_data !== $v) {
            $this->customization_data = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA] = true;
        }

        return $this;
    }

    /**
     * Set the value of [price] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPrice(?string $v = null): static
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_PRICE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [taxed_price] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTaxedPrice(?string $v = null): static
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->taxed_price !== $v) {
            $this->taxed_price = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_TAXED_PRICE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [quantity] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setQuantity(?string $v = null): static
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->quantity !== $v) {
            $this->quantity = $v;
            $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_QUANTITY] = true;
        }

        return $this;
    }

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return bool Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues(): bool
    {
            if ($this->price !== '0.000000') {
                return false;
            }

            if ($this->taxed_price !== '0.000000') {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    }

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by DataFetcher->fetch().
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param bool $rehydrate Whether this object is being re-hydrated from the database.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int next starting column
     * @throws \Propel\Runtime\Exception\PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(array $row, int $startcol = 0, bool $rehydrate = false, string $indexType = TableMap::TYPE_NUM): int
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('ProductAvailableOptionId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->product_available_option_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('CartItemOptionId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->cart_item_option_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('OrderProductId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->order_product_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('OptionOrderProductId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->option_order_product_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('CustomizationData', TableMap::TYPE_PHPNAME, $indexType)];
            $this->customization_data = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('Price', TableMap::TYPE_PHPNAME, $indexType)];
            $this->price = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('TaxedPrice', TableMap::TYPE_PHPNAME, $indexType)];
            $this->taxed_price = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : OptionCartItemOrderProductTableMap::translateFieldName('Quantity', TableMap::TYPE_PHPNAME, $indexType)];
            $this->quantity = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 9; // 9 = OptionCartItemOrderProductTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Option\\Model\\OptionCartItemOrderProduct'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function ensureConsistency(): void
    {
        if ($this->aProductAvailableOption !== null && $this->product_available_option_id !== $this->aProductAvailableOption->getId()) {
            $this->aProductAvailableOption = null;
        }
        if ($this->aCartItem !== null && $this->cart_item_option_id !== $this->aCartItem->getId()) {
            $this->aCartItem = null;
        }
        if ($this->aOrderProductRelatedByOrderProductId !== null && $this->order_product_id !== $this->aOrderProductRelatedByOrderProductId->getId()) {
            $this->aOrderProductRelatedByOrderProductId = null;
        }
        if ($this->aOrderProductRelatedByOptionOrderProductId !== null && $this->option_order_product_id !== $this->aOrderProductRelatedByOptionOrderProductId->getId()) {
            $this->aOrderProductRelatedByOptionOrderProductId = null;
        }
    }

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param bool $deep (optional) Whether to also de-associated any related objects.
     * @param ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload(bool $deep = false, ?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildOptionCartItemOrderProductQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aCartItem = null;
            $this->aProductAvailableOption = null;
            $this->aOrderProductRelatedByOrderProductId = null;
            $this->aOrderProductRelatedByOptionOrderProductId = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see OptionCartItemOrderProduct::setDeleted()
     * @see OptionCartItemOrderProduct::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildOptionCartItemOrderProductQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    public function save(?ConnectionInterface $con = null): int
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionCartItemOrderProductTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                OptionCartItemOrderProductTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con): int
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aCartItem !== null) {
                if ($this->aCartItem->isModified() || $this->aCartItem->isNew()) {
                    $affectedRows += $this->aCartItem->save($con);
                }
                $this->setCartItem($this->aCartItem);
            }

            if ($this->aProductAvailableOption !== null) {
                if ($this->aProductAvailableOption->isModified() || $this->aProductAvailableOption->isNew()) {
                    $affectedRows += $this->aProductAvailableOption->save($con);
                }
                $this->setProductAvailableOption($this->aProductAvailableOption);
            }

            if ($this->aOrderProductRelatedByOrderProductId !== null) {
                if ($this->aOrderProductRelatedByOrderProductId->isModified() || $this->aOrderProductRelatedByOrderProductId->isNew()) {
                    $affectedRows += $this->aOrderProductRelatedByOrderProductId->save($con);
                }
                $this->setOrderProductRelatedByOrderProductId($this->aOrderProductRelatedByOrderProductId);
            }

            if ($this->aOrderProductRelatedByOptionOrderProductId !== null) {
                if ($this->aOrderProductRelatedByOptionOrderProductId->isModified() || $this->aOrderProductRelatedByOptionOrderProductId->isNew()) {
                    $affectedRows += $this->aOrderProductRelatedByOptionOrderProductId->save($con);
                }
                $this->setOrderProductRelatedByOptionOrderProductId($this->aOrderProductRelatedByOptionOrderProductId);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    }

    /**
     * Insert the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con): void
    {
        $modifiedColumns = [];
        $index = 0;

        $this->modifiedColumns[OptionCartItemOrderProductTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . OptionCartItemOrderProductTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID)) {
            $modifiedColumns[':p' . $index++]  = '`product_available_option_id`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID)) {
            $modifiedColumns[':p' . $index++]  = '`cart_item_option_id`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`order_product_id`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`option_order_product_id`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA)) {
            $modifiedColumns[':p' . $index++]  = '`customization_data`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`price`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`taxed_price`';
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_QUANTITY)) {
            $modifiedColumns[':p' . $index++]  = '`quantity`';
        }

        $sql = sprintf(
            'INSERT INTO `option_cart_item_order_product` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id`':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);

                        break;
                    case '`product_available_option_id`':
                        $stmt->bindValue($identifier, $this->product_available_option_id, PDO::PARAM_INT);

                        break;
                    case '`cart_item_option_id`':
                        $stmt->bindValue($identifier, $this->cart_item_option_id, PDO::PARAM_INT);

                        break;
                    case '`order_product_id`':
                        $stmt->bindValue($identifier, $this->order_product_id, PDO::PARAM_INT);

                        break;
                    case '`option_order_product_id`':
                        $stmt->bindValue($identifier, $this->option_order_product_id, PDO::PARAM_INT);

                        break;
                    case '`customization_data`':
                        $stmt->bindValue($identifier, $this->customization_data, PDO::PARAM_STR);

                        break;
                    case '`price`':
                        $stmt->bindValue($identifier, $this->price, PDO::PARAM_STR);

                        break;
                    case '`taxed_price`':
                        $stmt->bindValue($identifier, $this->taxed_price, PDO::PARAM_STR);

                        break;
                    case '`quantity`':
                        $stmt->bindValue($identifier, $this->quantity, PDO::PARAM_STR);

                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @return int Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con): int
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName(string $name, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = OptionCartItemOrderProductTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos Position in XML schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition(int $pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();

            case 1:
                return $this->getProductAvailableOptionId();

            case 2:
                return $this->getCartItemOptionId();

            case 3:
                return $this->getOrderProductId();

            case 4:
                return $this->getOptionOrderProductId();

            case 5:
                return $this->getCustomizationData();

            case 6:
                return $this->getPrice();

            case 7:
                return $this->getTaxedPrice();

            case 8:
                return $this->getQuantity();

            default:
                return null;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param bool $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array An associative array containing the field names (as keys) and field values
     */
    public function toArray(string $keyType = TableMap::TYPE_PHPNAME, bool $includeLazyLoadColumns = true, array $alreadyDumpedObjects = [], bool $includeForeignObjects = false): array
    {
        if (isset($alreadyDumpedObjects['OptionCartItemOrderProduct'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['OptionCartItemOrderProduct'][$this->hashCode()] = true;
        $keys = OptionCartItemOrderProductTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getProductAvailableOptionId(),
            $keys[2] => $this->getCartItemOptionId(),
            $keys[3] => $this->getOrderProductId(),
            $keys[4] => $this->getOptionOrderProductId(),
            $keys[5] => $this->getCustomizationData(),
            $keys[6] => $this->getPrice(),
            $keys[7] => $this->getTaxedPrice(),
            $keys[8] => $this->getQuantity(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aCartItem) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'cartItem';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'cart_item';
                        break;
                    default:
                        $key = 'CartItem';
                }

                $result[$key] = $this->aCartItem->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aProductAvailableOption) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'productAvailableOption';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'product_available_option';
                        break;
                    default:
                        $key = 'ProductAvailableOption';
                }

                $result[$key] = $this->aProductAvailableOption->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aOrderProductRelatedByOrderProductId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'orderProduct';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'order_product';
                        break;
                    default:
                        $key = 'OrderProduct';
                }

                $result[$key] = $this->aOrderProductRelatedByOrderProductId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aOrderProductRelatedByOptionOrderProductId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'orderProduct';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'order_product';
                        break;
                    default:
                        $key = 'OrderProduct';
                }

                $result[$key] = $this->aOrderProductRelatedByOptionOrderProductId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this
     */
    public function setByName(string $name, $value, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = OptionCartItemOrderProductTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        $this->setByPosition($pos, $value);

        return $this;
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return $this
     */
    public function setByPosition(int $pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setProductAvailableOptionId($value);
                break;
            case 2:
                $this->setCartItemOptionId($value);
                break;
            case 3:
                $this->setOrderProductId($value);
                break;
            case 4:
                $this->setOptionOrderProductId($value);
                break;
            case 5:
                $this->setCustomizationData($value);
                break;
            case 6:
                $this->setPrice($value);
                break;
            case 7:
                $this->setTaxedPrice($value);
                break;
            case 8:
                $this->setQuantity($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param array $arr An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return $this
     */
    public function fromArray(array $arr, string $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = OptionCartItemOrderProductTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setProductAvailableOptionId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setCartItemOptionId($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setOrderProductId($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setOptionOrderProductId($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setCustomizationData($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setPrice($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setTaxedPrice($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setQuantity($arr[$keys[8]]);
        }

        return $this;
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this The current object, for fluid interface
     */
    public function importFrom($parser, string $data, string $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria(): Criteria
    {
        $criteria = new Criteria(OptionCartItemOrderProductTableMap::DATABASE_NAME);

        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_ID)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_PRODUCT_AVAILABLE_OPTION_ID, $this->product_available_option_id);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_CART_ITEM_OPTION_ID, $this->cart_item_option_id);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_ORDER_PRODUCT_ID, $this->order_product_id);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_OPTION_ORDER_PRODUCT_ID, $this->option_order_product_id);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_CUSTOMIZATION_DATA, $this->customization_data);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_PRICE)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_PRICE, $this->price);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_TAXED_PRICE, $this->taxed_price);
        }
        if ($this->isColumnModified(OptionCartItemOrderProductTableMap::COL_QUANTITY)) {
            $criteria->add(OptionCartItemOrderProductTableMap::COL_QUANTITY, $this->quantity);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria(): Criteria
    {
        $criteria = ChildOptionCartItemOrderProductQuery::create();
        $criteria->add(OptionCartItemOrderProductTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int|string Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param int|null $key Primary key.
     * @return void
     */
    public function setPrimaryKey(?int $key = null): void
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of \Option\Model\OptionCartItemOrderProduct (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setProductAvailableOptionId($this->getProductAvailableOptionId());
        $copyObj->setCartItemOptionId($this->getCartItemOptionId());
        $copyObj->setOrderProductId($this->getOrderProductId());
        $copyObj->setOptionOrderProductId($this->getOptionOrderProductId());
        $copyObj->setCustomizationData($this->getCustomizationData());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setTaxedPrice($this->getTaxedPrice());
        $copyObj->setQuantity($this->getQuantity());
        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Option\Model\OptionCartItemOrderProduct Clone of current object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function copy(bool $deepCopy = false)
    {
        // we use \get_class(), because this might be a subclass
        $clazz = \get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a CartItem object.
     *
     * @param CartItem|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setCartItem(?CartItem $v = null)
    {
        if ($v === null) {
            $this->setCartItemOptionId(NULL);
        } else {
            $this->setCartItemOptionId($v->getId());
        }

        $this->aCartItem = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the CartItem object, it will not be re-added.
        if ($v !== null) {
            $v->addOptionCartItemOrderProduct($this);
        }


        return $this;
    }


    /**
     * Get the associated CartItem object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return CartItem|null The associated CartItem object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getCartItem(?ConnectionInterface $con = null)
    {
        if ($this->aCartItem === null && ($this->cart_item_option_id != 0)) {
            $this->aCartItem = CartItemQuery::create()->findPk($this->cart_item_option_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCartItem->addOptionCartItemOrderProducts($this);
             */
        }

        return $this->aCartItem;
    }

    /**
     * Declares an association between this object and a ChildProductAvailableOption object.
     *
     * @param ChildProductAvailableOption|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setProductAvailableOption(?ChildProductAvailableOption $v = null)
    {
        if ($v === null) {
            $this->setProductAvailableOptionId(NULL);
        } else {
            $this->setProductAvailableOptionId($v->getId());
        }

        $this->aProductAvailableOption = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildProductAvailableOption object, it will not be re-added.
        if ($v !== null) {
            $v->addOptionCartItemOrderProduct($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildProductAvailableOption object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildProductAvailableOption|null The associated ChildProductAvailableOption object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getProductAvailableOption(?ConnectionInterface $con = null)
    {
        if ($this->aProductAvailableOption === null && ($this->product_available_option_id != 0)) {
            $this->aProductAvailableOption = ChildProductAvailableOptionQuery::create()->findPk($this->product_available_option_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aProductAvailableOption->addOptionCartItemOrderProducts($this);
             */
        }

        return $this->aProductAvailableOption;
    }

    /**
     * Declares an association between this object and a OrderProduct object.
     *
     * @param OrderProduct|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setOrderProductRelatedByOrderProductId(?OrderProduct $v = null)
    {
        if ($v === null) {
            $this->setOrderProductId(NULL);
        } else {
            $this->setOrderProductId($v->getId());
        }

        $this->aOrderProductRelatedByOrderProductId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the OrderProduct object, it will not be re-added.
        if ($v !== null) {
            $v->addOptionCartItemOrderProductRelatedByOrderProductId($this);
        }


        return $this;
    }


    /**
     * Get the associated OrderProduct object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return OrderProduct|null The associated OrderProduct object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getOrderProductRelatedByOrderProductId(?ConnectionInterface $con = null)
    {
        if ($this->aOrderProductRelatedByOrderProductId === null && ($this->order_product_id != 0)) {
            $this->aOrderProductRelatedByOrderProductId = OrderProductQuery::create()->findPk($this->order_product_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aOrderProductRelatedByOrderProductId->addOptionCartItemOrderProductsRelatedByOrderProductId($this);
             */
        }

        return $this->aOrderProductRelatedByOrderProductId;
    }

    /**
     * Declares an association between this object and a OrderProduct object.
     *
     * @param OrderProduct|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setOrderProductRelatedByOptionOrderProductId(?OrderProduct $v = null)
    {
        if ($v === null) {
            $this->setOptionOrderProductId(NULL);
        } else {
            $this->setOptionOrderProductId($v->getId());
        }

        $this->aOrderProductRelatedByOptionOrderProductId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the OrderProduct object, it will not be re-added.
        if ($v !== null) {
            $v->addOptionCartItemOrderProductRelatedByOptionOrderProductId($this);
        }


        return $this;
    }


    /**
     * Get the associated OrderProduct object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return OrderProduct|null The associated OrderProduct object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getOrderProductRelatedByOptionOrderProductId(?ConnectionInterface $con = null)
    {
        if ($this->aOrderProductRelatedByOptionOrderProductId === null && ($this->option_order_product_id != 0)) {
            $this->aOrderProductRelatedByOptionOrderProductId = OrderProductQuery::create()->findPk($this->option_order_product_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aOrderProductRelatedByOptionOrderProductId->addOptionCartItemOrderProductsRelatedByOptionOrderProductId($this);
             */
        }

        return $this->aOrderProductRelatedByOptionOrderProductId;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     *
     * @return $this
     */
    public function clear()
    {
        if (null !== $this->aCartItem) {
            $this->aCartItem->removeOptionCartItemOrderProduct($this);
        }
        if (null !== $this->aProductAvailableOption) {
            $this->aProductAvailableOption->removeOptionCartItemOrderProduct($this);
        }
        if (null !== $this->aOrderProductRelatedByOrderProductId) {
            $this->aOrderProductRelatedByOrderProductId->removeOptionCartItemOrderProductRelatedByOrderProductId($this);
        }
        if (null !== $this->aOrderProductRelatedByOptionOrderProductId) {
            $this->aOrderProductRelatedByOptionOrderProductId->removeOptionCartItemOrderProductRelatedByOptionOrderProductId($this);
        }
        $this->id = null;
        $this->product_available_option_id = null;
        $this->cart_item_option_id = null;
        $this->order_product_id = null;
        $this->option_order_product_id = null;
        $this->customization_data = null;
        $this->price = null;
        $this->taxed_price = null;
        $this->quantity = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);

        return $this;
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param bool $deep Whether to also clear the references on all referrer objects.
     * @return $this
     */
    public function clearAllReferences(bool $deep = false)
    {
        if ($deep) {
        } // if ($deep)

        $this->aCartItem = null;
        $this->aProductAvailableOption = null;
        $this->aOrderProductRelatedByOrderProductId = null;
        $this->aOrderProductRelatedByOptionOrderProductId = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(OptionCartItemOrderProductTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preSave(?ConnectionInterface $con = null): bool
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $event = new OptionCartItemOrderProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionCartItemOrderProductEvent::PRE_SAVE
                );

            return !$event->isPropagationStopped();
        }

        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postSave(?ConnectionInterface $con = null): void
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $con->getEventDispatcher()
                ->dispatch(
                    new OptionCartItemOrderProductEvent($this),
                    OptionCartItemOrderProductEvent::POST_SAVE
                );
        }
    }

    /**
     * Code to be run before inserting to database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preInsert(?ConnectionInterface $con = null): bool
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $event = new OptionCartItemOrderProductEvent($this);
            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionCartItemOrderProductEvent::PRE_INSERT
                );

            return !$event->isPropagationStopped();
        }

        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postInsert(?ConnectionInterface $con = null): void
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $con->getEventDispatcher()
                ->dispatch(
                    new OptionCartItemOrderProductEvent($this),
                    OptionCartItemOrderProductEvent::POST_INSERT
                );
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preUpdate(?ConnectionInterface $con = null): bool
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $event = new OptionCartItemOrderProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionCartItemOrderProductEvent::PRE_UPDATE
                );

            return !$event->isPropagationStopped();
        }

        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postUpdate(?ConnectionInterface $con = null): void
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $con->getEventDispatcher()
                ->dispatch(
                    new OptionCartItemOrderProductEvent($this),
                    OptionCartItemOrderProductEvent::POST_UPDATE
                );
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preDelete(?ConnectionInterface $con = null): bool
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $event = new OptionCartItemOrderProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionCartItemOrderProductEvent::PRE_DELETE
                );

            return !$event->isPropagationStopped();
        }

        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postDelete(?ConnectionInterface $con = null): void
    {

        if (null !== $con
            && method_exists($con, 'getEventDispatcher')
            && null !== $con->getEventDispatcher()
        ) {
            $con->getEventDispatcher()
                ->dispatch(
                    new OptionCartItemOrderProductEvent($this),
                    OptionCartItemOrderProductEvent::POST_DELETE
                );
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);
            $inputData = $params[0];
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->importFrom($format, $inputData, $keyType);
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = $params[0] ?? true;
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->exportTo($format, $includeLazyLoadColumns, $keyType);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
