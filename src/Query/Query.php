<?php
declare(strict_types=1);
namespace Feather\Query;

use Feather\Base;
/**
 * Query class
 */
class Query extends Base {
    /**
     * 
     * @var array array of Conditions
     */
    protected $where;


    /**
     * @var bool control of processing of params
     */
    protected $paramsProcessed = false;

    /**
     * @var array params, values and types
     */
    protected $params;

    


    public function where($condition) {
        $this->where = Condition::analyze($condition);
        return $this;
    }

    /**
     * Return where conditions
     */
    public function getWhere() {
        /*if (!$this->paramsProcessed) {
            $this->processParams();
        }*/
        return $this->where;
    }

    /**
     * Array of params and respective values and datatypes
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Process where conditions, generating params
     */
    protected function processParams() {
        $this->params = [];
        if (!is_null($this->where)) {
            ["params"=>$this->params,"conditions"=>$this->where] = $this->analyzeParams($this->where);

            if ($this->log) $this->log->info('PARAMS',$this->params);
            if ($this->log) $this->log->info('WHERE',$this->where);

            $this->paramsProcessed = true;
        }
    }

    /**
     * run over conditions and process all params
     * 
     * return [
     *  "params"=>params...,
     *  "condition"=>conditions...
     * ]
     * 
     * @param array $condition
     * @return array processed params and proccessed conditions
     */
    protected function analyzeParams(array $condition):array {
        $stack = [];

        $params = [];
        $paramsCount = 0;

        $stack[] =& $condition;


        while ($c = array_pop($stack)) {
            if ($c['hasChilds']) {
                foreach ($c['operands'] as $operand) {
                    $stack[] =& $operand;
                }
            }
            if ($c['hasValue']) {

                if ((is_array($c['value'])) || (!isset($c['value']['key']))) {
                    $c['value'] = $this->analyzeValue($c['value']);
                }

                
                if ($c['value']['type'] == Type::ARRAY) {
                    $t = count($c['value']['value']);
                    for($i = 0;$i < $t;$i++) {
                        $c['value']['value'][$i]['key'] = $paramsCount++;
                        $params[] = $c['value']['value'][$i];
                    }
                }

                $c['value']['key'] = $paramsCount++;
                $params[] = $c['value'];
                
            }
        }

        return [
            'params'=>$params,
            'conditions'=>$condition
        ];
    }

    
}