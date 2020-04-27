<?php
declare(strict_types=1);
namespace Feather\Builder;

use \Feather\Query\Condition;
use \Feather\Type;

class ConditionsBuilder extends \Feather\Base {
    protected static $patternDate = '/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})$/s';
    protected static $patternTime = '/^([0-9]{1,2}):([0-9]{1,2})(:([0-9]{1,2}))?$/s';
    protected static $patternDateTime = '/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})\ ([0-9]{1,2}):([0-9]{1,2})(:([0-9]{1,2}))?$/s';
    protected static $patternDateUTC = '/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})T([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})(Z|[\+\-][0-9]+:[0-9]{1,2})$/s';

    protected $paramBuilder;
    protected $driver;
    /**
     * Cache of operators words
     */
    protected $operatorsWords;

    /**
     * 
     * @return string conditions params
     * @throws Feather\Exceptions\DriverNotSetException when driver not set or not found
     */
    public function build(array $condition) {
        if (is_null($this->operatorsWords)) {
            $this->operatorsWords = $this->driver->getOperatorsWords();
        }
        if (($this->driver instanceof \Feather\Driver\DriverInterface) && isset($this->operatorsWords[$condition['operator']])) {
            //logical operators
            if (in_array($condition['operator'],Condition::$logicalOperators)) {
                return $this->buildLogicalOperator($condition);
            } else {
                return $this->buildRelationalOperator($condition);
            }
        } else {
            throw new \Feather\Exceptions\DriverNotSetException();
        }
    }

    /**
     * Build logical operators string
     */
    protected function buildLogicalOperator(array $condition) {
        $operands = [];
        foreach ($condition['operands'] as $op) {
            $operands[] = '('
                .$this->build($op)
                .')';
        }

        if ($condition['operator'] == Condition::OP_NOT) {
            return $this->operatorsWords[$condition['operator']].' '.$operands[0];
        } else {
            return implode(' '.$this->operatorsWords[$condition['operator']].' ',$operands);
        }

    }


    /**
     * Build relational operators string
     */
    protected function buildRelationalOperator(array $condition) {
        switch ($condition['operator']) {
            case Condition::OP_BETWEEN:
            case Condition::OP_NOT_BETWEEN:
                return $this->buildRelationalOperatorBetween($condition);
            break;

            case Condition::OP_FULL_LIKE:
            case Condition::OP_BOTH_LIKE:
            case Condition::OP_LEFT_LIKE:
            case Condition::OP_RIGHT_LIKE:

                return $this->driver->equivLike($condition['operands'][0],$this->resolveValue($condition['value']));
            break;
            
            default:
                return $this->buildDefaultOperator($condition);
            break;
        }
    }

    protected function buildRelationalOperatorBetween(array $condition) {
        /**
         * column BETWEEN :value1 AND :value2
         * column BETWEEN column1 AND :value
         * column BETWEEN column2 AND column3
         */

        //all columns will as string
        $betweenParams = [];
        foreach ($condition['operands'] as $op) $betweenParams[] = $op;

        if ($condition['hasValue']) {
            foreach ($condition['value'] as $value) {
                $betweenParams[] = $this->resolveValue($value);
            }
        }

        return $this->driver->equivBetween(...$betweenParams);        
    }

    protected function buildDefaultOperator(array $condition) {

        switch ($condition['operandsCount']) {
            case 1:
                return $condition['operands'][0]
                    .' '
                    .$this->operatorsWords[$condition['operator']]
                    .' '
                    .$this->resolveValue($condition['value']);
            break;
            case 2:
                return $condition['operands'][0]
                    .' '
                    .$this->operatorsWords[$condition['operator']]
                    .' '
                    .$condition['operands'][1];
            break;

            default:
                return '';
            break;
        }
    }

    protected function resolveValue($rawValue):string {
        $value = $this->analyzeValue($rawValue);
        if ($value['type'] == Type::QUERY) {
            return $this->createNew()->build($value['value']);
        } elseif ($value['type'] == Type::ARRAY) {
            $values = [];            
            foreach ($value['value'] as $key => $v) {
                $values[] = $this->resolveValue($v);
            }
            return $this->driver->equivArray($values);
        } else {
            return $this->paramBuilder->addParam($value);
        }
    }

    /**
     * Analyze value, returning processed value and type
     * 
     * return [
     *  'type'=>Type::STRING|Type::INT|Type::DATE ...
     *  'value'=>VALUE
     * ]
     * 
     * @param string $value
     * 
     * @return array Array 
     * @throws Feather\Exceptions\InvalidValueException when a value isn't of any type in Type class
     */
    protected function analyzeValue($value) {
        if (is_int($value)) {
            $type = Type::INT;
        } elseif (is_float($value)) {
            $type = Type::FLOAT;
        } elseif (is_bool($value)) {
            $type = Type::BOOL;
        } elseif (is_null($value)) {
            $type = Type::NULL;
        } elseif (is_array($value)) {
            $type = Type::ARRAY;
            $nValue = [];
            foreach ($value as $k=>$v) {
                $nValue[] = $this->analyzeValue($v);
            }
            $value = $nValue;
        } elseif ($value instanceof Query) {
            $type = Type::QUERY;
        } elseif (is_string($value)) {
            if (preg_match(self::$patternDate,$value,$matches)) {
                $type = Type::DATE;
                $value = [
                    'year'=>$matches[1],
                    'month'=>$matches[2],
                    'day'=>$matches[3],
                 ];
            } elseif (preg_match(self::$patternTime,$value,$matches)) {
                $type = Type::TIME;
                $value = [
                    'hour'=>$matches[1],
                    'minute'=>$matches[2],
                    'second'=>$matches[4]??0,
                 ];
            } elseif (preg_match(self::$patternDateTime,$value,$matches)) {
                $type = Type::DATETIME;
                $value = [
                    'year'=>$matches[1],
                    'month'=>$matches[2],
                    'day'=>$matches[3],
                    'hour'=>$matches[4],
                    'minute'=>$matches[5],
                    'second'=>$matches[7]??0,
                 ];
            } elseif (preg_match(self::$patternDateUTC,$value,$matches)) {
                $type = Type::DATETIMEUTC;
                $value = [
                    'year'=>$matches[1],
                    'month'=>$matches[2],
                    'day'=>$matches[3],
                    'hour'=>$matches[4],
                    'minute'=>$matches[5],
                    'second'=>$matches[6],
                    'offset'=>($matches[7] == 'Z')?(0):($matches[7])
                 ];
            } else {
                $type = Type::STRING;
            }

            
        } else {
            throw new InvalidValueException($value);
        }

        return [
            'type'=>$type,
            'value'=>$value
        ];
    }
}