<?php
declare(strict_types=1);
namespace Feather\Builder;

class ParamBuilder extends \Feather\Base {
    protected $values;
    protected $paramsCount = 0;

    const PREFIX = ':p';

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

    public function clearparams() {
        $this->values = [];
        $this->paramsCount = 0;
    }

    public function getParams() {
        return $this->value;
    }

    public function getCount() {
        return $this->paramsCount;
    }
}