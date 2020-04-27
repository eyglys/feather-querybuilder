<?php 

use Feather\Query\Query;

use Faker\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class QueryTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $faker;

    protected $log;

    
    protected function _before()
    {
        $this->faker = Factory::create();
        $this->log = new Logger('Query');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../runtime/debug_info.log', Logger::INFO));
    }

    protected function _after()
    {
    }

    public function testHeritage() {
        $q = new \Feather\Query\Query();
        $this->tester->assertTrue($q instanceof \Feather\Statement);
    }

    public function testMinimalTest() {
        $q = new Query();
        $q->select('column1');

        $this->tester->assertFalse($q->isBuildable());

        $q = new Query();
        $q->from('table');

        $this->tester->assertFalse($q->isBuildable());

        $q = new Query();
        $q->select('column1')->from('table');

        $this->tester->assertTrue($q->isBuildable());
        
    }
}