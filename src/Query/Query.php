<?php
declare(strict_types=1);
namespace Feather\Query;

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
     * @param array $tables
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
     * @return bool True if is Buildable
     */
    public function isBuildable() {
        return !empty($this->select) && !empty($this->from);
    }

    
}