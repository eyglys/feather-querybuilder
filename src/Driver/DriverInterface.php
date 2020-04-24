<?php

namespace Feather\Driver;


/**
 * Driver interface
 */
interface DriverInterface {

    /**
     * Return a array with all operators and words of Database drived
     * 
     * Example:
     * [
     *  Condition::OP_AND => 'AND'
     *  Condition::OP_OR => 'OR'
     *  //...
     * ]
     * @return array
     */
    public static function getOperatorsWords();

    /**
     * Return SGBD equivalent of BETWEEN
     * 
     * param1 BETWEEN param2 AND param3
     * 
     * @param mixed $param1 first param
     */
    public static function equivBetween($param1,$param2,$param3);

    /**
     * Return SGBD equivalent of LIKE
     * 
     * param1 LIKE 'param2'
     * 
     * @param mixed $param1
     * @param mixed $param2
     */
    public static function equivLike($param1,$param2);

    public static function equivArray(array $array);

    public static function convertToDate($value);

    public static function convertToDateTime($value);

    public static function convertToDateTimeUTC($value);

    
}