<?php

namespace Option\Model\Base;

use \Exception;
use \PDO;
use Option\Model\CategoryAvailableOption as ChildCategoryAvailableOption;
use Option\Model\CategoryAvailableOptionQuery as ChildCategoryAvailableOptionQuery;
use Option\Model\OptionProduct as ChildOptionProduct;
use Option\Model\OptionProductQuery as ChildOptionProductQuery;
use Option\Model\ProductAvailableOption as ChildProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery as ChildProductAvailableOptionQuery;
use Option\Model\TemplateAvailableOption as ChildTemplateAvailableOption;
use Option\Model\TemplateAvailableOptionQuery as ChildTemplateAvailableOptionQuery;
use Option\Model\Event\OptionProductEvent;
use Option\Model\Map\CategoryAvailableOptionTableMap;
use Option\Model\Map\OptionProductTableMap;
use Option\Model\Map\ProductAvailableOptionTableMap;
use Option\Model\Map\TemplateAvailableOptionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

/**
 * Base class that represents a row from the 'option_product' table.
 *
 *
 *
 * @package    propel.generator.Option.Model.Base
 */
abstract class OptionProduct implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Option\\Model\\Map\\OptionProductTableMap';


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
     * The value for the product_id field.
     *
     * @var        int
     */
    protected ?int $product_id = null;

    /**
     * The value for the is_customizable field.
     *
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected ?bool $is_customizable = null;

    /**
     * @var        Product
     */
    protected $aProduct;

    /**
     * @var        ObjectCollection|ChildProductAvailableOption[] Collection to store aggregation of ChildProductAvailableOption objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildProductAvailableOption> Collection to store aggregation of ChildProductAvailableOption objects.
     */
    protected $collProductAvailableOptions;
    protected $collProductAvailableOptionsPartial;

    /**
     * @var        ObjectCollection|ChildCategoryAvailableOption[] Collection to store aggregation of ChildCategoryAvailableOption objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildCategoryAvailableOption> Collection to store aggregation of ChildCategoryAvailableOption objects.
     */
    protected $collCategoryAvailableOptions;
    protected $collCategoryAvailableOptionsPartial;

    /**
     * @var        ObjectCollection|ChildTemplateAvailableOption[] Collection to store aggregation of ChildTemplateAvailableOption objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildTemplateAvailableOption> Collection to store aggregation of ChildTemplateAvailableOption objects.
     */
    protected $collTemplateAvailableOptions;
    protected $collTemplateAvailableOptionsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildProductAvailableOption[]
     * @phpstan-var ObjectCollection&\Traversable<ChildProductAvailableOption>
     */
    protected $productAvailableOptionsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildCategoryAvailableOption[]
     * @phpstan-var ObjectCollection&\Traversable<ChildCategoryAvailableOption>
     */
    protected $categoryAvailableOptionsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildTemplateAvailableOption[]
     * @phpstan-var ObjectCollection&\Traversable<ChildTemplateAvailableOption>
     */
    protected $templateAvailableOptionsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues(): void
    {
        $this->is_customizable = false;
    }

    /**
     * Initializes internal state of Option\Model\Base\OptionProduct object.
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
     * Compares this with another <code>OptionProduct</code> instance.  If
     * <code>obj</code> is an instance of <code>OptionProduct</code>, delegates to
     * <code>equals(OptionProduct)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [product_id] column value.
     *
     * @return int
     */
    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    /**
     * Get the [is_customizable] column value.
     *
     * @return boolean
     */
    public function getIsCustomizable(): ?bool
    {
        return $this->is_customizable;
    }

    /**
     * Get the [is_customizable] column value.
     *
     * @return boolean
     */
    public function isCustomizable(): ?bool
    {
        return $this->getIsCustomizable();
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
            $this->modifiedColumns[OptionProductTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [product_id] column.
     *
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setProductId(?int $v = null): static
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->product_id !== $v) {
            $this->product_id = $v;
            $this->modifiedColumns[OptionProductTableMap::COL_PRODUCT_ID] = true;
        }

        if ($this->aProduct !== null && $this->aProduct->getId() !== $v) {
            $this->aProduct = null;
        }

        return $this;
    }

    /**
     * Sets the value of the [is_customizable] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param bool|integer|string $v The new value
     * @return $this The current object (for fluent API support)
     */
    public function setIsCustomizable($v): static
    {
        if ($v !== null) {
            if (\is_string($v)) {
                $v = \in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_customizable !== $v) {
            $this->is_customizable = $v;
            $this->modifiedColumns[OptionProductTableMap::COL_IS_CUSTOMIZABLE] = true;
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
            if ($this->is_customizable !== false) {
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : OptionProductTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : OptionProductTableMap::translateFieldName('ProductId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->product_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : OptionProductTableMap::translateFieldName('IsCustomizable', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_customizable = (null !== $col) ? (boolean) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 3; // 3 = OptionProductTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Option\\Model\\OptionProduct'), 0, $e);
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
        if ($this->aProduct !== null && $this->product_id !== $this->aProduct->getId()) {
            $this->aProduct = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(OptionProductTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildOptionProductQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aProduct = null;
            $this->collProductAvailableOptions = null;

            $this->collCategoryAvailableOptions = null;

            $this->collTemplateAvailableOptions = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see OptionProduct::setDeleted()
     * @see OptionProduct::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(OptionProductTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildOptionProductQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(OptionProductTableMap::DATABASE_NAME);
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
                OptionProductTableMap::addInstanceToPool($this);
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

            if ($this->aProduct !== null) {
                if ($this->aProduct->isModified() || $this->aProduct->isNew()) {
                    $affectedRows += $this->aProduct->save($con);
                }
                $this->setProduct($this->aProduct);
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

            if ($this->productAvailableOptionsScheduledForDeletion !== null) {
                if (!$this->productAvailableOptionsScheduledForDeletion->isEmpty()) {
                    \Option\Model\ProductAvailableOptionQuery::create()
                        ->filterByPrimaryKeys($this->productAvailableOptionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->productAvailableOptionsScheduledForDeletion = null;
                }
            }

            if ($this->collProductAvailableOptions !== null) {
                foreach ($this->collProductAvailableOptions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->categoryAvailableOptionsScheduledForDeletion !== null) {
                if (!$this->categoryAvailableOptionsScheduledForDeletion->isEmpty()) {
                    \Option\Model\CategoryAvailableOptionQuery::create()
                        ->filterByPrimaryKeys($this->categoryAvailableOptionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->categoryAvailableOptionsScheduledForDeletion = null;
                }
            }

            if ($this->collCategoryAvailableOptions !== null) {
                foreach ($this->collCategoryAvailableOptions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->templateAvailableOptionsScheduledForDeletion !== null) {
                if (!$this->templateAvailableOptionsScheduledForDeletion->isEmpty()) {
                    \Option\Model\TemplateAvailableOptionQuery::create()
                        ->filterByPrimaryKeys($this->templateAvailableOptionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->templateAvailableOptionsScheduledForDeletion = null;
                }
            }

            if ($this->collTemplateAvailableOptions !== null) {
                foreach ($this->collTemplateAvailableOptions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
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

        $this->modifiedColumns[OptionProductTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . OptionProductTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(OptionProductTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(OptionProductTableMap::COL_PRODUCT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`product_id`';
        }
        if ($this->isColumnModified(OptionProductTableMap::COL_IS_CUSTOMIZABLE)) {
            $modifiedColumns[':p' . $index++]  = '`is_customizable`';
        }

        $sql = sprintf(
            'INSERT INTO `option_product` (%s) VALUES (%s)',
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
                    case '`product_id`':
                        $stmt->bindValue($identifier, $this->product_id, PDO::PARAM_INT);

                        break;
                    case '`is_customizable`':
                        $stmt->bindValue($identifier, (int) $this->is_customizable, PDO::PARAM_INT);

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
        $pos = OptionProductTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getProductId();

            case 2:
                return $this->getIsCustomizable();

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
        if (isset($alreadyDumpedObjects['OptionProduct'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['OptionProduct'][$this->hashCode()] = true;
        $keys = OptionProductTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getProductId(),
            $keys[2] => $this->getIsCustomizable(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aProduct) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'product';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'product';
                        break;
                    default:
                        $key = 'Product';
                }

                $result[$key] = $this->aProduct->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collProductAvailableOptions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'productAvailableOptions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'product_available_options';
                        break;
                    default:
                        $key = 'ProductAvailableOptions';
                }

                $result[$key] = $this->collProductAvailableOptions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCategoryAvailableOptions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'categoryAvailableOptions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'category_available_options';
                        break;
                    default:
                        $key = 'CategoryAvailableOptions';
                }

                $result[$key] = $this->collCategoryAvailableOptions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTemplateAvailableOptions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'templateAvailableOptions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'template_available_options';
                        break;
                    default:
                        $key = 'TemplateAvailableOptions';
                }

                $result[$key] = $this->collTemplateAvailableOptions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = OptionProductTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setProductId($value);
                break;
            case 2:
                $this->setIsCustomizable($value);
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
        $keys = OptionProductTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setProductId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setIsCustomizable($arr[$keys[2]]);
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
        $criteria = new Criteria(OptionProductTableMap::DATABASE_NAME);

        if ($this->isColumnModified(OptionProductTableMap::COL_ID)) {
            $criteria->add(OptionProductTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(OptionProductTableMap::COL_PRODUCT_ID)) {
            $criteria->add(OptionProductTableMap::COL_PRODUCT_ID, $this->product_id);
        }
        if ($this->isColumnModified(OptionProductTableMap::COL_IS_CUSTOMIZABLE)) {
            $criteria->add(OptionProductTableMap::COL_IS_CUSTOMIZABLE, $this->is_customizable);
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
        $criteria = ChildOptionProductQuery::create();
        $criteria->add(OptionProductTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Option\Model\OptionProduct (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setProductId($this->getProductId());
        $copyObj->setIsCustomizable($this->getIsCustomizable());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getProductAvailableOptions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addProductAvailableOption($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCategoryAvailableOptions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCategoryAvailableOption($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTemplateAvailableOptions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTemplateAvailableOption($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

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
     * @return \Option\Model\OptionProduct Clone of current object.
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
     * Declares an association between this object and a Product object.
     *
     * @param Product $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setProduct(?Product $v = null)
    {
        if ($v === null) {
            $this->setProductId(NULL);
        } else {
            $this->setProductId($v->getId());
        }

        $this->aProduct = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Product object, it will not be re-added.
        if ($v !== null) {
            $v->addOptionProduct($this);
        }


        return $this;
    }


    /**
     * Get the associated Product object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return Product The associated Product object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getProduct(?ConnectionInterface $con = null)
    {
        if ($this->aProduct === null && ($this->product_id != 0)) {
            $this->aProduct = ProductQuery::create()->findPk($this->product_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aProduct->addOptionProducts($this);
             */
        }

        return $this->aProduct;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName): void
    {
        if ('ProductAvailableOption' === $relationName) {
            $this->initProductAvailableOptions();
            return;
        }
        if ('CategoryAvailableOption' === $relationName) {
            $this->initCategoryAvailableOptions();
            return;
        }
        if ('TemplateAvailableOption' === $relationName) {
            $this->initTemplateAvailableOptions();
            return;
        }
    }

    /**
     * Clears out the collProductAvailableOptions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addProductAvailableOptions()
     */
    public function clearProductAvailableOptions()
    {
        $this->collProductAvailableOptions = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collProductAvailableOptions collection loaded partially.
     *
     * @return void
     */
    public function resetPartialProductAvailableOptions($v = true): void
    {
        $this->collProductAvailableOptionsPartial = $v;
    }

    /**
     * Initializes the collProductAvailableOptions collection.
     *
     * By default this just sets the collProductAvailableOptions collection to an empty array (like clearcollProductAvailableOptions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initProductAvailableOptions(bool $overrideExisting = true): void
    {
        if (null !== $this->collProductAvailableOptions && !$overrideExisting) {
            return;
        }

        $collectionClassName = ProductAvailableOptionTableMap::getTableMap()->getCollectionClassName();

        $this->collProductAvailableOptions = new $collectionClassName;
        $this->collProductAvailableOptions->setModel('\Option\Model\ProductAvailableOption');
    }

    /**
     * Gets an array of ChildProductAvailableOption objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildOptionProduct is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildProductAvailableOption[] List of ChildProductAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildProductAvailableOption> List of ChildProductAvailableOption objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getProductAvailableOptions(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collProductAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collProductAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collProductAvailableOptions) {
                    $this->initProductAvailableOptions();
                } else {
                    $collectionClassName = ProductAvailableOptionTableMap::getTableMap()->getCollectionClassName();

                    $collProductAvailableOptions = new $collectionClassName;
                    $collProductAvailableOptions->setModel('\Option\Model\ProductAvailableOption');

                    return $collProductAvailableOptions;
                }
            } else {
                $collProductAvailableOptions = ChildProductAvailableOptionQuery::create(null, $criteria)
                    ->filterByOptionProduct($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collProductAvailableOptionsPartial && count($collProductAvailableOptions)) {
                        $this->initProductAvailableOptions(false);

                        foreach ($collProductAvailableOptions as $obj) {
                            if (false == $this->collProductAvailableOptions->contains($obj)) {
                                $this->collProductAvailableOptions->append($obj);
                            }
                        }

                        $this->collProductAvailableOptionsPartial = true;
                    }

                    return $collProductAvailableOptions;
                }

                if ($partial && $this->collProductAvailableOptions) {
                    foreach ($this->collProductAvailableOptions as $obj) {
                        if ($obj->isNew()) {
                            $collProductAvailableOptions[] = $obj;
                        }
                    }
                }

                $this->collProductAvailableOptions = $collProductAvailableOptions;
                $this->collProductAvailableOptionsPartial = false;
            }
        }

        return $this->collProductAvailableOptions;
    }

    /**
     * Sets a collection of ChildProductAvailableOption objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $productAvailableOptions A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setProductAvailableOptions(Collection $productAvailableOptions, ?ConnectionInterface $con = null)
    {
        /** @var ChildProductAvailableOption[] $productAvailableOptionsToDelete */
        $productAvailableOptionsToDelete = $this->getProductAvailableOptions(new Criteria(), $con)->diff($productAvailableOptions);


        $this->productAvailableOptionsScheduledForDeletion = $productAvailableOptionsToDelete;

        foreach ($productAvailableOptionsToDelete as $productAvailableOptionRemoved) {
            $productAvailableOptionRemoved->setOptionProduct(null);
        }

        $this->collProductAvailableOptions = null;
        foreach ($productAvailableOptions as $productAvailableOption) {
            $this->addProductAvailableOption($productAvailableOption);
        }

        $this->collProductAvailableOptions = $productAvailableOptions;
        $this->collProductAvailableOptionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ProductAvailableOption objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ProductAvailableOption objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countProductAvailableOptions(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collProductAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collProductAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collProductAvailableOptions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getProductAvailableOptions());
            }

            $query = ChildProductAvailableOptionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByOptionProduct($this)
                ->count($con);
        }

        return count($this->collProductAvailableOptions);
    }

    /**
     * Method called to associate a ChildProductAvailableOption object to this object
     * through the ChildProductAvailableOption foreign key attribute.
     *
     * @param ChildProductAvailableOption $l ChildProductAvailableOption
     * @return $this The current object (for fluent API support)
     */
    public function addProductAvailableOption(ChildProductAvailableOption $l)
    {
        if ($this->collProductAvailableOptions === null) {
            $this->initProductAvailableOptions();
            $this->collProductAvailableOptionsPartial = true;
        }

        if (!$this->collProductAvailableOptions->contains($l)) {
            $this->doAddProductAvailableOption($l);

            if ($this->productAvailableOptionsScheduledForDeletion and $this->productAvailableOptionsScheduledForDeletion->contains($l)) {
                $this->productAvailableOptionsScheduledForDeletion->remove($this->productAvailableOptionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildProductAvailableOption $productAvailableOption The ChildProductAvailableOption object to add.
     */
    protected function doAddProductAvailableOption(ChildProductAvailableOption $productAvailableOption): void
    {
        $this->collProductAvailableOptions[]= $productAvailableOption;
        $productAvailableOption->setOptionProduct($this);
    }

    /**
     * @param ChildProductAvailableOption $productAvailableOption The ChildProductAvailableOption object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeProductAvailableOption(ChildProductAvailableOption $productAvailableOption)
    {
        if ($this->getProductAvailableOptions()->contains($productAvailableOption)) {
            $pos = $this->collProductAvailableOptions->search($productAvailableOption);
            $this->collProductAvailableOptions->remove($pos);
            if (null === $this->productAvailableOptionsScheduledForDeletion) {
                $this->productAvailableOptionsScheduledForDeletion = clone $this->collProductAvailableOptions;
                $this->productAvailableOptionsScheduledForDeletion->clear();
            }
            $this->productAvailableOptionsScheduledForDeletion[]= clone $productAvailableOption;
            $productAvailableOption->setOptionProduct(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this OptionProduct is new, it will return
     * an empty collection; or if this OptionProduct has previously
     * been saved, it will retrieve related ProductAvailableOptions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in OptionProduct.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildProductAvailableOption[] List of ChildProductAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildProductAvailableOption}> List of ChildProductAvailableOption objects
     */
    public function getProductAvailableOptionsJoinProduct(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildProductAvailableOptionQuery::create(null, $criteria);
        $query->joinWith('Product', $joinBehavior);

        return $this->getProductAvailableOptions($query, $con);
    }

    /**
     * Clears out the collCategoryAvailableOptions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addCategoryAvailableOptions()
     */
    public function clearCategoryAvailableOptions()
    {
        $this->collCategoryAvailableOptions = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collCategoryAvailableOptions collection loaded partially.
     *
     * @return void
     */
    public function resetPartialCategoryAvailableOptions($v = true): void
    {
        $this->collCategoryAvailableOptionsPartial = $v;
    }

    /**
     * Initializes the collCategoryAvailableOptions collection.
     *
     * By default this just sets the collCategoryAvailableOptions collection to an empty array (like clearcollCategoryAvailableOptions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCategoryAvailableOptions(bool $overrideExisting = true): void
    {
        if (null !== $this->collCategoryAvailableOptions && !$overrideExisting) {
            return;
        }

        $collectionClassName = CategoryAvailableOptionTableMap::getTableMap()->getCollectionClassName();

        $this->collCategoryAvailableOptions = new $collectionClassName;
        $this->collCategoryAvailableOptions->setModel('\Option\Model\CategoryAvailableOption');
    }

    /**
     * Gets an array of ChildCategoryAvailableOption objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildOptionProduct is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildCategoryAvailableOption[] List of ChildCategoryAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildCategoryAvailableOption> List of ChildCategoryAvailableOption objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getCategoryAvailableOptions(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collCategoryAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collCategoryAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collCategoryAvailableOptions) {
                    $this->initCategoryAvailableOptions();
                } else {
                    $collectionClassName = CategoryAvailableOptionTableMap::getTableMap()->getCollectionClassName();

                    $collCategoryAvailableOptions = new $collectionClassName;
                    $collCategoryAvailableOptions->setModel('\Option\Model\CategoryAvailableOption');

                    return $collCategoryAvailableOptions;
                }
            } else {
                $collCategoryAvailableOptions = ChildCategoryAvailableOptionQuery::create(null, $criteria)
                    ->filterByOptionProduct($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCategoryAvailableOptionsPartial && count($collCategoryAvailableOptions)) {
                        $this->initCategoryAvailableOptions(false);

                        foreach ($collCategoryAvailableOptions as $obj) {
                            if (false == $this->collCategoryAvailableOptions->contains($obj)) {
                                $this->collCategoryAvailableOptions->append($obj);
                            }
                        }

                        $this->collCategoryAvailableOptionsPartial = true;
                    }

                    return $collCategoryAvailableOptions;
                }

                if ($partial && $this->collCategoryAvailableOptions) {
                    foreach ($this->collCategoryAvailableOptions as $obj) {
                        if ($obj->isNew()) {
                            $collCategoryAvailableOptions[] = $obj;
                        }
                    }
                }

                $this->collCategoryAvailableOptions = $collCategoryAvailableOptions;
                $this->collCategoryAvailableOptionsPartial = false;
            }
        }

        return $this->collCategoryAvailableOptions;
    }

    /**
     * Sets a collection of ChildCategoryAvailableOption objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $categoryAvailableOptions A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setCategoryAvailableOptions(Collection $categoryAvailableOptions, ?ConnectionInterface $con = null)
    {
        /** @var ChildCategoryAvailableOption[] $categoryAvailableOptionsToDelete */
        $categoryAvailableOptionsToDelete = $this->getCategoryAvailableOptions(new Criteria(), $con)->diff($categoryAvailableOptions);


        $this->categoryAvailableOptionsScheduledForDeletion = $categoryAvailableOptionsToDelete;

        foreach ($categoryAvailableOptionsToDelete as $categoryAvailableOptionRemoved) {
            $categoryAvailableOptionRemoved->setOptionProduct(null);
        }

        $this->collCategoryAvailableOptions = null;
        foreach ($categoryAvailableOptions as $categoryAvailableOption) {
            $this->addCategoryAvailableOption($categoryAvailableOption);
        }

        $this->collCategoryAvailableOptions = $categoryAvailableOptions;
        $this->collCategoryAvailableOptionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CategoryAvailableOption objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related CategoryAvailableOption objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countCategoryAvailableOptions(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collCategoryAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collCategoryAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCategoryAvailableOptions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCategoryAvailableOptions());
            }

            $query = ChildCategoryAvailableOptionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByOptionProduct($this)
                ->count($con);
        }

        return count($this->collCategoryAvailableOptions);
    }

    /**
     * Method called to associate a ChildCategoryAvailableOption object to this object
     * through the ChildCategoryAvailableOption foreign key attribute.
     *
     * @param ChildCategoryAvailableOption $l ChildCategoryAvailableOption
     * @return $this The current object (for fluent API support)
     */
    public function addCategoryAvailableOption(ChildCategoryAvailableOption $l)
    {
        if ($this->collCategoryAvailableOptions === null) {
            $this->initCategoryAvailableOptions();
            $this->collCategoryAvailableOptionsPartial = true;
        }

        if (!$this->collCategoryAvailableOptions->contains($l)) {
            $this->doAddCategoryAvailableOption($l);

            if ($this->categoryAvailableOptionsScheduledForDeletion and $this->categoryAvailableOptionsScheduledForDeletion->contains($l)) {
                $this->categoryAvailableOptionsScheduledForDeletion->remove($this->categoryAvailableOptionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildCategoryAvailableOption $categoryAvailableOption The ChildCategoryAvailableOption object to add.
     */
    protected function doAddCategoryAvailableOption(ChildCategoryAvailableOption $categoryAvailableOption): void
    {
        $this->collCategoryAvailableOptions[]= $categoryAvailableOption;
        $categoryAvailableOption->setOptionProduct($this);
    }

    /**
     * @param ChildCategoryAvailableOption $categoryAvailableOption The ChildCategoryAvailableOption object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeCategoryAvailableOption(ChildCategoryAvailableOption $categoryAvailableOption)
    {
        if ($this->getCategoryAvailableOptions()->contains($categoryAvailableOption)) {
            $pos = $this->collCategoryAvailableOptions->search($categoryAvailableOption);
            $this->collCategoryAvailableOptions->remove($pos);
            if (null === $this->categoryAvailableOptionsScheduledForDeletion) {
                $this->categoryAvailableOptionsScheduledForDeletion = clone $this->collCategoryAvailableOptions;
                $this->categoryAvailableOptionsScheduledForDeletion->clear();
            }
            $this->categoryAvailableOptionsScheduledForDeletion[]= clone $categoryAvailableOption;
            $categoryAvailableOption->setOptionProduct(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this OptionProduct is new, it will return
     * an empty collection; or if this OptionProduct has previously
     * been saved, it will retrieve related CategoryAvailableOptions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in OptionProduct.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildCategoryAvailableOption[] List of ChildCategoryAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildCategoryAvailableOption}> List of ChildCategoryAvailableOption objects
     */
    public function getCategoryAvailableOptionsJoinCategory(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildCategoryAvailableOptionQuery::create(null, $criteria);
        $query->joinWith('Category', $joinBehavior);

        return $this->getCategoryAvailableOptions($query, $con);
    }

    /**
     * Clears out the collTemplateAvailableOptions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addTemplateAvailableOptions()
     */
    public function clearTemplateAvailableOptions()
    {
        $this->collTemplateAvailableOptions = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collTemplateAvailableOptions collection loaded partially.
     *
     * @return void
     */
    public function resetPartialTemplateAvailableOptions($v = true): void
    {
        $this->collTemplateAvailableOptionsPartial = $v;
    }

    /**
     * Initializes the collTemplateAvailableOptions collection.
     *
     * By default this just sets the collTemplateAvailableOptions collection to an empty array (like clearcollTemplateAvailableOptions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTemplateAvailableOptions(bool $overrideExisting = true): void
    {
        if (null !== $this->collTemplateAvailableOptions && !$overrideExisting) {
            return;
        }

        $collectionClassName = TemplateAvailableOptionTableMap::getTableMap()->getCollectionClassName();

        $this->collTemplateAvailableOptions = new $collectionClassName;
        $this->collTemplateAvailableOptions->setModel('\Option\Model\TemplateAvailableOption');
    }

    /**
     * Gets an array of ChildTemplateAvailableOption objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildOptionProduct is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildTemplateAvailableOption[] List of ChildTemplateAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildTemplateAvailableOption> List of ChildTemplateAvailableOption objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getTemplateAvailableOptions(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collTemplateAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collTemplateAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collTemplateAvailableOptions) {
                    $this->initTemplateAvailableOptions();
                } else {
                    $collectionClassName = TemplateAvailableOptionTableMap::getTableMap()->getCollectionClassName();

                    $collTemplateAvailableOptions = new $collectionClassName;
                    $collTemplateAvailableOptions->setModel('\Option\Model\TemplateAvailableOption');

                    return $collTemplateAvailableOptions;
                }
            } else {
                $collTemplateAvailableOptions = ChildTemplateAvailableOptionQuery::create(null, $criteria)
                    ->filterByOptionProduct($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collTemplateAvailableOptionsPartial && count($collTemplateAvailableOptions)) {
                        $this->initTemplateAvailableOptions(false);

                        foreach ($collTemplateAvailableOptions as $obj) {
                            if (false == $this->collTemplateAvailableOptions->contains($obj)) {
                                $this->collTemplateAvailableOptions->append($obj);
                            }
                        }

                        $this->collTemplateAvailableOptionsPartial = true;
                    }

                    return $collTemplateAvailableOptions;
                }

                if ($partial && $this->collTemplateAvailableOptions) {
                    foreach ($this->collTemplateAvailableOptions as $obj) {
                        if ($obj->isNew()) {
                            $collTemplateAvailableOptions[] = $obj;
                        }
                    }
                }

                $this->collTemplateAvailableOptions = $collTemplateAvailableOptions;
                $this->collTemplateAvailableOptionsPartial = false;
            }
        }

        return $this->collTemplateAvailableOptions;
    }

    /**
     * Sets a collection of ChildTemplateAvailableOption objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $templateAvailableOptions A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setTemplateAvailableOptions(Collection $templateAvailableOptions, ?ConnectionInterface $con = null)
    {
        /** @var ChildTemplateAvailableOption[] $templateAvailableOptionsToDelete */
        $templateAvailableOptionsToDelete = $this->getTemplateAvailableOptions(new Criteria(), $con)->diff($templateAvailableOptions);


        $this->templateAvailableOptionsScheduledForDeletion = $templateAvailableOptionsToDelete;

        foreach ($templateAvailableOptionsToDelete as $templateAvailableOptionRemoved) {
            $templateAvailableOptionRemoved->setOptionProduct(null);
        }

        $this->collTemplateAvailableOptions = null;
        foreach ($templateAvailableOptions as $templateAvailableOption) {
            $this->addTemplateAvailableOption($templateAvailableOption);
        }

        $this->collTemplateAvailableOptions = $templateAvailableOptions;
        $this->collTemplateAvailableOptionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related TemplateAvailableOption objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related TemplateAvailableOption objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countTemplateAvailableOptions(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collTemplateAvailableOptionsPartial && !$this->isNew();
        if (null === $this->collTemplateAvailableOptions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTemplateAvailableOptions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getTemplateAvailableOptions());
            }

            $query = ChildTemplateAvailableOptionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByOptionProduct($this)
                ->count($con);
        }

        return count($this->collTemplateAvailableOptions);
    }

    /**
     * Method called to associate a ChildTemplateAvailableOption object to this object
     * through the ChildTemplateAvailableOption foreign key attribute.
     *
     * @param ChildTemplateAvailableOption $l ChildTemplateAvailableOption
     * @return $this The current object (for fluent API support)
     */
    public function addTemplateAvailableOption(ChildTemplateAvailableOption $l)
    {
        if ($this->collTemplateAvailableOptions === null) {
            $this->initTemplateAvailableOptions();
            $this->collTemplateAvailableOptionsPartial = true;
        }

        if (!$this->collTemplateAvailableOptions->contains($l)) {
            $this->doAddTemplateAvailableOption($l);

            if ($this->templateAvailableOptionsScheduledForDeletion and $this->templateAvailableOptionsScheduledForDeletion->contains($l)) {
                $this->templateAvailableOptionsScheduledForDeletion->remove($this->templateAvailableOptionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildTemplateAvailableOption $templateAvailableOption The ChildTemplateAvailableOption object to add.
     */
    protected function doAddTemplateAvailableOption(ChildTemplateAvailableOption $templateAvailableOption): void
    {
        $this->collTemplateAvailableOptions[]= $templateAvailableOption;
        $templateAvailableOption->setOptionProduct($this);
    }

    /**
     * @param ChildTemplateAvailableOption $templateAvailableOption The ChildTemplateAvailableOption object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeTemplateAvailableOption(ChildTemplateAvailableOption $templateAvailableOption)
    {
        if ($this->getTemplateAvailableOptions()->contains($templateAvailableOption)) {
            $pos = $this->collTemplateAvailableOptions->search($templateAvailableOption);
            $this->collTemplateAvailableOptions->remove($pos);
            if (null === $this->templateAvailableOptionsScheduledForDeletion) {
                $this->templateAvailableOptionsScheduledForDeletion = clone $this->collTemplateAvailableOptions;
                $this->templateAvailableOptionsScheduledForDeletion->clear();
            }
            $this->templateAvailableOptionsScheduledForDeletion[]= clone $templateAvailableOption;
            $templateAvailableOption->setOptionProduct(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this OptionProduct is new, it will return
     * an empty collection; or if this OptionProduct has previously
     * been saved, it will retrieve related TemplateAvailableOptions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in OptionProduct.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildTemplateAvailableOption[] List of ChildTemplateAvailableOption objects
     * @phpstan-return ObjectCollection&\Traversable<ChildTemplateAvailableOption}> List of ChildTemplateAvailableOption objects
     */
    public function getTemplateAvailableOptionsJoinTemplate(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildTemplateAvailableOptionQuery::create(null, $criteria);
        $query->joinWith('Template', $joinBehavior);

        return $this->getTemplateAvailableOptions($query, $con);
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
        if (null !== $this->aProduct) {
            $this->aProduct->removeOptionProduct($this);
        }
        $this->id = null;
        $this->product_id = null;
        $this->is_customizable = null;
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
            if ($this->collProductAvailableOptions) {
                foreach ($this->collProductAvailableOptions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCategoryAvailableOptions) {
                foreach ($this->collCategoryAvailableOptions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTemplateAvailableOptions) {
                foreach ($this->collTemplateAvailableOptions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collProductAvailableOptions = null;
        $this->collCategoryAvailableOptions = null;
        $this->collTemplateAvailableOptions = null;
        $this->aProduct = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(OptionProductTableMap::DEFAULT_STRING_FORMAT);
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
            $event = new OptionProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionProductEvent::PRE_SAVE
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
                    new OptionProductEvent($this),
                    OptionProductEvent::POST_SAVE
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
            $event = new OptionProductEvent($this);
            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionProductEvent::PRE_INSERT
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
                    new OptionProductEvent($this),
                    OptionProductEvent::POST_INSERT
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
            $event = new OptionProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionProductEvent::PRE_UPDATE
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
                    new OptionProductEvent($this),
                    OptionProductEvent::POST_UPDATE
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
            $event = new OptionProductEvent($this);

            $con->getEventDispatcher()
                ->dispatch(
                    $event,
                    OptionProductEvent::PRE_DELETE
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
                    new OptionProductEvent($this),
                    OptionProductEvent::POST_DELETE
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
