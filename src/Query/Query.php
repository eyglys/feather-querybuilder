<?php
declare(strict_types=1);
namespace Feather\Query;

use Feather\Exceptions\InvalidValueException;

/**
 * Query class
 */
class Query extends \Feather\Statement {
    /**
     * list of columns
     *
     * @var array
     */
    public $select;
    
    /**
     * list of table/s
     * @var array
     */
    public $from;

    /**
     * list of Conditions
     * @var array 
     */
    public $where;

    /**
     * Limit of results
     *
     * @var int|null
     */
    public $limit = null;

    /**
     * Offset of results
     *
     * @var int|null
     */
    public $offset = null;

    /**
     * Order of results
     *
     * @var array|null
     */
    public $orderBy = null;

    /**
     * Constant of order ASC
     */
    const ORDER_ASC = 'ASC';

    /**
     * Constant of oder DESC
     */
    const ORDER_DESC = 'DESC';

    /**
     * Select clause
     * 
     * select('name') -> SELECT name
     * 
     * select([
     *  'name',
     *  'age'
     * ]) -> SELECT name, ages
     * 
     * select
     * 
     * @param string|array $columns list of columns
     * @return Query
     */
    public function select($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->select = $columns;
        return $this;
    }

    public function where($condition) {
        $this->where = Condition::analyze($condition);
        return $this;
    }

    /**
     * FROM clause
     * 
     * @param string|array $tables
     * 
     * @return Query
     */
    public function from($tables) {
        if (!is_array($tables)) {
            $tables = [$tables];
        }
        $this->from = $tables;
        return $this;
    }

    /**
     * Set LIMIT clause
     *
     * @param int|null $limit limit of results
     * @return Query
     */
    public function limit(?int $limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * set OFFSET of results
     *
     * @param int|null $offset
     * @return Query
     */
    public function offset(?int $offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * set ORDER BY clause
     *
     * @param string|array $columns
     * @return Query
     * 
     * @throws Feather\Exceptions\InvalidValueException when order value is invalid
     */
    public function orderBy($columns) {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->orderBy = $this->validateAndTransformOrderBy($columns);
        return $this;
    }

    /**
     * @return bool True if is Buildable
     */
    public function isBuildable() {
        return !empty($this->select) && !empty($this->from);
    }

    /**
     * Validate if order is in format:
     * 
     * [
     *  'column1'=>Query::ORDER_DESC,
     *  'column2'=>Query::ORDER_ASC,
     *  'column3'
     * ]
     *
     * @param array $columns order columns
     * @return array 
     * [
     *  'column1'=>Query::ORDER_DESC,
     *  'column2'=>Query::ORDER_ASC,
     *  'column3'=>null
     * ]
     * 
     * @throws Feather\Exceptions\InvalidValueException when order value is invalid
     */
    protected function validateAndTransformOrderBy($columns) {
        foreach ($columns as $key=>$value) {
            if (!is_string($key)) {
                $columns[$value] = null;
                unset($columns[$key]);
            } elseif (($value != self::ORDER_ASC) && ($value != self::ORDER_DESC)) {
                throw new InvalidValueException($value);
            }
        }

        return $columns;
    }
    
}