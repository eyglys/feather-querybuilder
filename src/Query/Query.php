<?php
declare(strict_types=1);
namespace Feather\Query;

/**
 * Query class
 */
class Query extends \Feather\Statement {
    public $select;
    
    /**
     * list of table/s
     */
    public $from;

    /**
     * 
     * @var array list of Conditions
     */
    public $where;

    public function where($condition) {
        $this->where = Condition::analyze($condition);
        return $this;
    }

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
    public function select($columns) {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->select = $columns;
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
     * @return bool True if is Buildable
     */
    public function isBuildable() {
        return !empty($this->select) && !empty($this->from);
    }

    
}