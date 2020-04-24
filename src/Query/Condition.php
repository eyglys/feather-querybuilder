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

    protected static $patternLogicalOperators = '/^('.self::OP_AND.'|'.self::OP_OR.'|'.self::OP_NOT.'|'.self::OP_XOR.')$/is';

    /**
     * Analyze a single expression
     * @param string $expression
     * @return array null if is invalid, array with column(s), by writing order, and operator
     * 
     */
    protected static function analyzeExpression(string $expression):?array {
        if (preg_match(self::$patternExpression,$expression,$matches)) {

            $result = [
                'operator'=>mb_strtoupper(trim($matches[2])),
                'operands'=>[trim($matches[1])],
                'operandsCount' =>1,
                'hasValue'=>false,
                'hasChilds'=>false,
            ];
            if (in_array($result['operator'],self::$relationalOperators)) {
                $matches[3] = trim($matches[3]);
                if (!empty($matches[3])) {
                    $result['operands'][] = $matches[3];
                    $result['operandsCount']++;

                    //analyse specific cases
                    return self::reanalyzeExpression($result);
                }
                return $result;
            }
        } else {
            if (preg_match(self::$patternLogicalOperators,$expression,$matches)) {
                return [
                    'operator'=>mb_strtoupper(trim($matches[1])),
                    'operands'=>[],
                    'operandsCount' =>0,
                    'hasValue'=>false,
                    'hasChilds'=>true,
                ];
            }
        }

        return null;
    }

    /**
     * Reanalyze columns considering existence of two or more columns
     * @param array $analyzeData result data of analyzeExpression
     * @return array processed data
     */
    protected static function reanalyzeExpression(array $analyzeData) {
        switch ($analyzeData['operator']) {
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                //$analyzeData[1] = second param, testing if in format column2[and]column2
                if (preg_match(self::$patternBeetweenOperands,$analyzeData['operands'][1],$matches)) {
                    $analyzeData['operands'] = [$analyzeData['operands'][0],$matches[1],$matches[2]];
                    $analyzeData['operandsCount'] = 3;
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
    protected static function likeTransform($operation,$value) {
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

        return $value;
    }

    /**
     * Transform value according to the operator
     * @param string $operator
     * @param mixed $value
     * @throws Feather\Exceptions\InvalidValueException when value is invalid
     */
    protected static function valueTransform(string $operator,$value) {
        switch ($operator) {
            case self::OP_FULL_LIKE:
            case self::OP_BOTH_LIKE:
            case self::OP_LEFT_LIKE:
            case self::OP_RIGHT_LIKE:
                return self::likeTransform($operator,$value);
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
            $count = count($condition);
            if ($count == 1) {
                $key = array_key_first($condition);
                $value = $condition[$key];
                if (is_string($key)) {

                    $data = self::analyze($key);
                    //logical operator (and|or|not|xor)
                    if ($data['hasChilds']) {
                        
                        if ($data['operator'] != self::OP_NOT) {
                            /**
                             * ['and'=>[
                             *      ['column1[>]'=>$value],
                             *      ['column2[<=]'=>$value2]
                             *  ]
                             * ]
                             */
                            if (!is_array($value) || (($operandsCount = count($value)) < 2)) {
                                throw new InvalidConditionException('Logical operator \''.$data['operator'].'\' needs at least 2 operands');
                            }
                        } else {
                            if (!is_array($value)) {
                                $value = [$value];
                            } elseif (count($value) != 1) throw new InvalidConditionException('Logical operator \''.$data['operator'].'\' needs exactly 1 operand');
                        }

                        foreach ($value as $internalKey =>$internalCondition) {
                            
                            if (is_string($internalKey)) $internalCondition = [$internalKey =>$internalCondition];

                            $data['operands'][] = self::analyze($internalCondition);
                            $data['operandsCount']++;
                        }
                    } else {
                        $data['value'] = self::valueTransform($data['operator'],$value);
                        $data['hasValue'] = true;
                    }

                    $result[] = $data;
                } elseif (is_int($key)) {
                    $tmpResult = self::analyze($value);
                    if ($tmpResult['hasChilds']) throw new InvalidConditionException('Logical operator \''.$tmpResult['operator'].'\' needs operand(s)');
                    else $result[] = $tmpResult;
                }
            } else {
                return self::analyze([
                    self::OP_AND=> $condition
                ]);
            }

            if (count($result) == 1) return $result[0];
            else return $result;
        }

        throw new InvalidConditionException($condition);
    }

}