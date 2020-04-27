<?php 
use Faker\Factory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Feather\Builder\Builder;
use Feather\Query\Query;
use Feather\Driver\MySQL\MySQL;

class BuilderTest extends \Codeception\Test\Unit
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
        $this->log = new Logger('Builder');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../runtime/debug_info.log', Logger::INFO));
    }

    protected function _after()
    {
    }

    public function testMinimal() {
        $query = new Query();
        $builder = new Builder(['driver'=>new MySQL(),'log'=>$this->log]);


        $this->tester->expectThrowable(Feather\Exceptions\BuilderException::class,function() use ($query,$builder) {
            $builder->build($query);
        });
    }

    
    public function testParamsCount()
    {
        $builder = new Builder(['driver'=>new MySQL(),'log'=>$this->log]);

        $totalParams = 0;
        $param = function($type = null) use (&$totalParams) {
            $totalParams++;
            if ($type == 'int') return rand(1,100);
            else return 'some'.rand(1,100);
        };

        $query = (new Query(['log'=>$this->log]))->select('column1')
        ->from('table')
        ->where([
            'and'=>[
                'column1[=]'=>$param('int'),
                'column2[btw]'=>[$param('int'),$param('int')],
                'or'=>[
                    'colum1[!=]'=>$param('int'),
                    'email[like]'=>$param()
                ]
            ]
        ]);

        $sql = $builder->build($query);

        $this->log->info('BUILDER, sql = '.$sql);
        $params = $builder->paramBuilder;

        $this->tester->assertEquals($totalParams,$params->getCount());

        //$this->log->info('BUILD = '.$sql);
    }
}