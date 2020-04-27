<?php
declare(strict_types=1);
namespace Feather\Builder;

use Feather\Exceptions\DriverException;
use Feather\Query\{Query, Condition, Type};
use Feather\Statement;

class Builder extends \Feather\Base {
    /**
     * Driver
     */
    public $driver = null;

    

    protected $paramBuilder;

    public function __construct($params = []) {
        //test if driver is set
        if (is_null($this->paramBuilder)) {
            $this->paramBuilder = new ParamBuilder();
        }
        

        return parent::__construct($params);
    }

    public function getParams():ParamBuilder {
        return $this->paramBuilder;
    }

    public function setDriver(DriverBase $driver) {
        $this->driver = $driver;
        
    }

    /**
     * Create a new QueryBuilder with same configurations
     */
    public function createNew() {
        return new self([
            'driver'=>$this->driver,
            'operatorsWords'=>$this->operatorsWords,
            'paramBuilder'=>$this->paramBuilder
        ]);
    }

    /**
     * Build a query
     * 
     * @throws Feather\Exceptions\DriverNotSetException when driver not set or not found
     */
    public function build(Statement $query) {
        if ($this->driver instanceof \Feather\Driver\DriverInterface) {
            
            $conditions = $query->getWhere();
            
            $conditionsBuilder = new ConditionsBuilder([
                'driver'=>$this->driver,
                'paramBuilder'=>$this->paramBuilder,
            ]);

            $build = $conditionsBuilder->build($conditions);

            return $build;
        } else {
            throw new \Feather\Exceptions\DriverNotSetException();
        }

    }

   
}