<?php
declare(strict_types=1);
namespace Feather\Driver;

abstract class DriverBase extends \Feather\Base {
    /**
     * Return SGBD equivalent of BETWEEN
     * 
     * param1 BETWEEN param2 AND param3
     * 
     * @param mixed $param1 first param
     */
    public static function equivBetween($param1,$param2,$param3) {
        return $param1.' BETWEEN '.$param2.' AND '.$param3;
    }

    /**
     * Return SGBD equivalent of LIKE
     * 
     * param1 LIKE 'param2'
     * 
     * @param mixed $param1
     * @param mixed $param2
     */
    public static function equivLike($param1,$param2) {
        return $param1.' LIKE '.$param2;
    }

    public static function equivArray(array $array) {
        return '('.implode(',',$array).')';
    }

    public static function convertToDate($value){

    }

    public static function convertToDateTime($value){

    }

    public static function convertToDateTimeUTC($value){

    }

    public static function where(string $conditions) {
        return 'WHERE '.$conditions;
    }

    public static function from($tables) {
        return 'FROM '.implode(', ',$tables);
    }

    public static function select($columns) {
        return 'SELECT '.implode(', ',$columns);
    }

    public static function alias($name,$alias) {
        return $name.' AS '.$alias;
    }
}