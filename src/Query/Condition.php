<?php
declare(strict_types=1);

namespace Feather\Query;

use Feather\Exceptions\InvalidConditionException;

class Condition extends \Feather\Base {


    //Logical operators
    const OP_AND = 'AND';
    const OP_OR = 'OR';
    const OP_NOT = 'NOT';
    const OP_XOR = 'XOR';
    const OP_FULL_LIKE = 'LIKE';
    const OP_BOTH_LIKE = '%LIKE%';
    const OP_LEFT_LIKE = '%LIKE';
    const OP_RIGHT_LIKE = 'LIKE%';
    const OP_BETWEEN = 'BTW';
    const OP_NOT_BETWEEN = '!BTW';
    const OP_IN = 'IN';
    const OP_NOT_IN = '!IN';
    const OP_IS = 'IS';
    const OP_IS_NOT = 'IS!';

    //Relational operators

    const OP_GREATER = '>';
    const OP_GREATER_OR_EQUAL = '>=';

    const OP_LESS = '<';
    const OP_LESS_OR_EQUAL = '<=';

    const OP_EQUAL = '=';
    const OP_DIFFER = '!=';

    public static $relationalOperators = [
        self::OP_FULL_LIKE,
        self::OP_BOTH_LIKE,
        self::OP_LEFT_LIKE,
        self::OP_RIGHT_LIKE,
        self::OP_BETWEEN,
        self::OP_NOT_BETWEEN,
        self::OP_IN,
        self::OP_NOT_IN,
        self::OP_IS,
        self::OP_IS_NOT,

        self::OP_GREATER,
        self::OP_GREATER_OR_EQUAL,

        self::OP_LESS,
        self::OP_LESS_OR_EQUAL,

        self::OP_EQUAL,
        self::OP_DIFFER,
    ];

    public static $logicalOperators = [
        self::OP_AND,
        self::OP_OR,
        self::OP_NOT,
        self::OP_XOR,
    ];

    /**
     * Regular expressions
     */

    public static $patternExpression = '/^([^\[\]\ ]+)[ ]*\[([^\]]+)\][ ]*(.*)$/is';

    public static $patternBeetweenOperands = '/^([^\[\]\ ]+)[ ]*\[and\][ ]*(.*)$/is';

    /**
     * Analyze a single expression
     * @param string $expression
     * @return array null if is invalid, array with column(s), by writing order, and operator
     * 
     */
    public static function analyzeExpression(string $expression):?array {
        if (preg_match(self::$patternExpression,$expression,$matches)) {

            $result = [
                'columns'=>[trim($matches[1])],
                'columnsCount' =>1,
                'operator'=>mb_strtoupper(trim($matches[2])),
                'hasValue'=>false
            ];
            if (in_array($result['operator'],self::$relationalOperators)) {
                $matches[3] = trim($matches[3]);
                if (!empty($matches[3])) {
                    $result['columns'][] = $matches[3];
                    $result['columnsCount']++;

                    //analyse specific cases
                    return self::reanalyzeExpression($result);
                }
                return $result;
            }
        }

        return null;
    }

    /**
     * Reanalyze columns considering existence of two or more columns
     * @param array $analyzeData result data of analyzeExpression
     * @return array processed data
     */
    public static function reanalyzeExpression(array $analyzeData) {
        switch ($analyzeData['operator']) {
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                //$analyzeData[1] = second param, testing if in format column2[and]column2
                if (preg_match(self::$patternBeetweenOperands,$analyzeData['columns'][1],$matches)) {
                    $analyzeData['columns'] = [$analyzeData['columns'][0],$matches[1],$matches[2]];
                    $analyzeData['columnsCount'] = 3;
                } 
            break;
        }

        return $analyzeData;
    }

    /**
     * Transform value of like condition
     * @param string $operation like operation
     * @param string $value value of like
     * @return string transformed value
     */
    public static function likeTransform($operation,$value) {
        $value = (string) $value;
        if (($operation[0] != '%') && ($operation[-1] != '%')) {
            return 
                '%'.
                mb_ereg_replace('[ ]+','%',trim($value))
                .'%';
        } else {
            if ($operation[0] == '%') $value = '%'.$value;
            if ($operation[-1] == '%') $value = $value.'%';
        }

        return [$value];
    }

    /**
     * Transform value according to the operation
     * @throws Feather\Exceptions\InvalidValueException when value is invalid
     */
    public static function valueTransform(string $operation,$value) {
        switch ($operation) {
            case self::OP_FULL_LIKE:
            case self::OP_BOTH_LIKE:
            case self::OP_LEFT_LIKE:
            case self::OP_RIGHT_LIKE:
                return self::likeTransform($operation,$value);
                break;

            case self::OP_NOT_IN:
            case self::OP_IN:
                if (!is_array($value) && !is_object($value)) $value = [$value];
                break;

            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                if (!is_array($value) || (count($value) != 2)) throw new InvalidValueException('BETWEEN operator need array with 2 items');
            break;

            default:
                return $value;
                break;
        }
        return $value;
    }

    /**
     * Validate and analyze condition
     * @param string|array $condition Condition to analise
     * @return array analyze array of conditions
     * @throws Feather\Exceptions\InvalidConditionException
     * @throws Feather\Exceptions\InvalidValueException
     */
    public static function analyze($condition):?array {
        //single comparison between columns
        if (is_string($condition)) {
            $result = self::analyzeExpression($condition);
            if (!is_null($result)) {
                return $result;
            }
            else throw new InvalidConditionException($condition);
        } elseif (is_array($condition)) {
            $keys = array_keys($condition);
            $values = array_values($condition);
            $result = [];

            foreach ($condition as $key => $value) {
                if (is_string($key)) {
                    $data = self::analyzeExpression($key);
                    if (!is_null($data)) {
                        $data['value'] = self::valueTransform($data['operator'],$value);
                        if (!is_array($data['value'])) $data['value'] = [$data['value']];
                        $data['hasValue'] = true;

                        $result[] = $data;
                    } else throw new InvalidConditionException($key);
                } elseif (is_int($key)) {
                    $result[] = self::analyze($value);
                }
            }

            if (count($result) == 1) return $result[0];
            else return $result;
        }

        throw new InvalidConditionException($condition);
    }

}