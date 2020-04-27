<?php
declare(strict_types=1);
namespace Feather\Query;

/**
 * Query class
 */
class Query extends \Feather\Statement {
    /**
     * 
     * @var array array of Conditions
     */
    protected $where;

    public function where($condition) {
        $this->where = Condition::analyze($condition);
        return $this;
    }

    /**
     * Return where conditions
     */
    public function getWhere() {
        return $this->where;
    }

    
}