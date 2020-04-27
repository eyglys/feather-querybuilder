<?php

namespace Test;

use Feather\Query\{Query,Condition};

use Faker\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DriverBase extends \Codeception\Test\Unit {

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $faker;

    protected $log;

    protected $driver;

    
    protected function _before()
    {
        $this->faker = Factory::create();
        $this->log = new Logger('Query');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../runtime/debug_info.log', Logger::INFO));

        parent::_before();
    }

    protected function _after()
    {
    }

    /**
     * Test if driver return all operators needed
     */
    public function testOperatorsReturn() {
        $this->tester->assertTrue($this->driver instanceof \Feather\Driver\DriverInterface);
        $arr = array_keys($this->driver->getOperatorsWords());

        $param = array_merge(Condition::$relationalOperators,Condition::$logicalOperators);

        sort($arr);
        sort($param);

        $this->tester->assertEquals($param,$arr);
    }


}