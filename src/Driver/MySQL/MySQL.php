<?php
declare(strict_types=1);
namespace Feather\Driver\MySQL;

use Feather\Query\Condition;
use Feather\Driver\{DriverBase, DriverInterface};

class MySQL extends DriverBase implements DriverInterface {
    public static function getOperatorsWords() {
        return [
            Condition::OP_AND => 'AND',
            Condition::OP_OR => 'OR',
            Condition::OP_NOT => 'NOT',
            Condition::OP_XOR => 'XOR',
            Condition::OP_FULL_LIKE => 'LIKE',
            Condition::OP_BOTH_LIKE => 'LIKE',
            Condition::OP_LEFT_LIKE => 'LIKE',
            Condition::OP_RIGHT_LIKE => 'LIKE',
            Condition::OP_BETWEEN => 'BETWEEN',
            Condition::OP_NOT_BETWEEN => 'NOT BETWEEN',
            Condition::OP_IN => 'IN',
            Condition::OP_NOT_IN => 'NOT IN',
            Condition::OP_IS => 'IS',
            Condition::OP_IS_NOT => 'IS NOT',

            //Relational operators

            Condition::OP_GREATER => '>',
            Condition::OP_GREATER_OR_EQUAL => '>=',

            Condition::OP_LESS => '<',
            Condition::OP_LESS_OR_EQUAL => '<=',

            Condition::OP_EQUAL => '=',
            Condition::OP_DIFFER => '!=',
        ];
    }


    /**
     * Configure pagination
     *
     * @param int|null $limit max of records
     * @param int|null $offset offset in record set
     * @return string representation of LIMIT and OFFSET
     */
    public static function setPage(?int $limit, ?int $offset) {
        #OFFSET without LIMIT
        #From MySQL docs: https://dev.mysql.com/doc/refman/8.0/en/select.html#sa21818891
        
        $result = '';
        if (!is_null($offset)) {
            if (is_null($limit)) {
                $limit = PHP_INT_MAX;
            }
        }
        
        if (!is_null($limit)) {
            $result .= 'LIMIT '.$limit;
            if (!is_null($offset)) {
                $result .=' OFFSET '.$offset;
            }
        }
        

        return $result;
    }
    
}