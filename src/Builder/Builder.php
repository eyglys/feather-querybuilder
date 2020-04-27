<?php
declare(strict_types=1);
namespace Feather\Builder;

use Feather\Exceptions\DriverException;
use Feather\Query\Query;
use Feather\Statement;

class Builder extends \Feather\Base {
    /**
     * Driver
     */
    public $driver = null;

    

    public $paramBuilder;

    public function __construct($params = []) {
        //test if driver is set
        if (is_null($this->paramBuilder) && !isset($params['paramBuilder'])) {
            $this->paramBuilder = new ParamBuilder();
        }
        

        return parent::__construct($params);
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
    public function build(Statement $st) {
        if ($this->driver instanceof \Feather\Driver\DriverInterface) {

            if ($st instanceof Query) {
                return (new QueryBuilder([
                    'driver'=>$this->driver,
                    'paramBuilder'=>$this->paramBuilder,
                    'builderOwner'=>$this
                ]))->build($st);
            }
        } else {
            throw new \Feather\Exceptions\DriverNotSetException();
        }

    }

   
}