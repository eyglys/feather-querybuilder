<?php
declare(strict_types=1);
namespace Feather\Builder;

class ParamBuilder extends \Feather\Base {
    private $values;
    private $paramsCount = 0;

    const PREFIX = ':param';

    /**
     * Return :paramNUMBER 
     * 
     * @param array $value value registered
     * @return string param to SQL
     */
    public function addParam(array $value):string {
        $param = self::PREFIX.$this->paramsCount++;
        $this->values[$param] = $value;
        return $param;
    }
}