<?php 
use Feather\Query\Condition;
use Faker\Factory;
use Feather\Exceptions\InvalidConditionException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class ConditionTest extends \Codeception\Test\Unit
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
        $this->log = new Logger('test');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/../runtime/debug_info.log', Logger::INFO));
    }

    protected function _after()
    {
    }


    
    public function testBasicOneColumnOperator()
    {
        foreach (Condition::$relationalOperators as $operator) {
            $column = $this->faker->lexify('column???');
            $expression = $column.'['.$operator.']';

            $data = Condition::analyze($expression);

            $this->tester->assertTrue($data['operandsCount'] == 1);
            $this->tester->assertTrue($data['operands'][0] == $column);
            $this->tester->assertTrue($data['operator'] == $operator);
        }
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testBasicTwoColumnsOperator()
    {
        foreach (Condition::$relationalOperators as $operator) {
            $column1 = $this->faker->lexify('column???');
            $column2 = $this->faker->lexify('column???');
            $expression = $column1.'['.$operator.']'.$column2;

            $data = Condition::analyze($expression);

            $this->tester->assertTrue($data['operandsCount'] == 2);
            $this->tester->assertTrue($data['operands'][0] == $column1);
            $this->tester->assertTrue($data['operands'][1] == $column2);
            $this->tester->assertTrue($data['operator'] == $operator);
        }
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testFullLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like]'=>'test1 test2'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%test1%test2%',$data['value'][0]);
    }
    /**
     * @depends testBasicOneColumnOperator
     */
    public function testBothLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like%]'=>'test'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%test%',$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testLeftLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like]'=>'test'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%test',$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testRightLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like%]'=>'test'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('test%',$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testIsNull() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[is]'=>null];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertNull($data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testIsValuable() {
        $column1 = $this->faker->lexify('column???');
        $constant = $this->faker->randomDigit;
        $expression = [$column1.'[is]'=>$constant];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($constant,$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testIsNotValuable() {
        $column1 = $this->faker->lexify('column???');
        $constant = $this->faker->randomDigit;
        $expression = [$column1.'[is!]'=>$constant];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($constant,$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testIsNotNull() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[is!]'=>null];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertNull($data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     * @expectedException InvalidConditionException
     */
    public function testWrontIsNot() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[!is]'=>null];

        $this->tester->expectThrowable(Feather\Exceptions\InvalidConditionException::class,function() use ($expression) {
            Condition::analyze($expression);
        });

    }

    /**
     * @depends testBasicOneColumnOperator
     * @expectedException InvalidConditionException
     */
    public function testWrongIn() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[in!]'=>null];

        $this->tester->expectThrowable(Feather\Exceptions\InvalidConditionException::class,function() use ($expression) {
            Condition::analyze($expression);
        });
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testInArray() {
        $column1 = $this->faker->lexify('column???');
        $value = [1,2,3];
        $expression = [$column1.'[in]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($data['value'],$value);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testInConstant() {
        $column1 = $this->faker->lexify('column???');
        $value = 4;
        $expression = [$column1.'[in]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($data['value'],[$value]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testInObject() {
        $column1 = $this->faker->lexify('column???');
        $value = new class{ public $tester; };
        $expression = [$column1.'[in]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertIsObject($data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testNotIn() {
        $column1 = $this->faker->lexify('column???');
        $value = [5,4,3];
        $expression = [$column1.'[!in]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($value,$data['value']);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testSimpleBeetween() {
        $column1 = $this->faker->lexify('column???');
        $value = [5,10];
        $expression = [$column1.'[btw]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($value,$data['value']);
    }
    /**
     * @depends testBasicOneColumnOperator
     */
    public function testSimpleNotBeetween() {
        $column1 = $this->faker->lexify('column???');
        $value = [5,10];
        $expression = [$column1.'[!btw]'=>$value];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals($value,$data['value']);
    }

    /**
     * @depends testBasicTwoColumnsOperator
     */
    public function testSimpleBeetweenTwoColumns() {
        $column1 = $this->faker->lexify('column???');
        $column2 = $this->faker->lexify('column???');
        $column3 = $this->faker->lexify('column???');
        $expression = $column1.'[btw]'.$column2.'[and]'.$column3;

        $data = Condition::analyze($expression);

        $this->tester->assertFalse($data['hasValue']);
        $this->tester->assertEquals(3,$data['operandsCount']);
        $this->tester->assertEquals([$column1,$column2,$column3],$data['operands']);
    }

    /**
     * @depends testBasicOneColumnOperator
     * @depends testSimpleBeetween
     */
    public function testLogicalOperators() {
        $column1 = $this->faker->lexify('column???');
        $column2 = $this->faker->lexify('column???');
        $column3 = $this->faker->lexify('column???');
        $value1 = rand(1,10);

        $operators = array_filter(Condition::$logicalOperators,fn($op) => $op != Condition::OP_NOT);
        foreach ($operators as $operator) {
            $expression = [
                mb_strtolower($operator) => [
                    //as array
                    [$column1.'[>]'=>$value1],
                    //as string
                    $column1.'[btw]'.$column2.'[and]'.$column3
                ]
            ];

            $data = Condition::analyze($expression);

            $this->tester->assertTrue($data['hasChilds']);
            $this->tester->assertEquals(2,$data['operandsCount']);

            //operand 1
            $this->tester->assertTrue($data['operands'][0]['hasValue']);
            $this->tester->assertEquals($value1,$data['operands'][0]['value'][0]);
            $this->tester->assertEquals(Condition::OP_GREATER,$data['operands'][0]['operator']);
            
            //operand 2
            $this->tester->assertFalse($data['operands'][1]['hasValue']);
            $this->tester->assertEquals(Condition::OP_BETWEEN,$data['operands'][1]['operator']);
            $this->tester->assertEquals([$column1,$column2,$column3],$data['operands'][1]['operands']);
        }
    }

    /**
     * @depends testLogicalOperators
     */
    public function testLogicalOperatorNot() {
        $column1 = $this->faker->lexify('column???');
        $column2 = $this->faker->lexify('column???');
        $column3 = $this->faker->lexify('column???');
        $value1 = rand(1,10);

        $expression = [
            mb_strtolower(Condition::OP_NOT) => [
                //as array
                [$column1.'[>]'=>$value1],
                //as string
                $column1.'[btw]'.$column2.'[and]'.$column3
            ]
        ];

        $this->tester->expectThrowable(Feather\Exceptions\InvalidConditionException::class,function() use ($expression) {
            Condition::analyze($expression);
        });

        $expression = [
            mb_strtolower(Condition::OP_NOT) => [
                //as array
                [$column1.'[>]'=>$value1],
            ]
        ];

        $data = Condition::analyze($expression);

        $this->tester->assertTrue($data['hasChilds']);
        $this->tester->assertEquals(1,$data['operandsCount']);
        $this->tester->assertTrue($data['operands'][0]['hasValue']);
        $this->tester->assertEquals($value1,$data['operands'][0]['value'][0]);
        $this->tester->assertEquals(Condition::OP_GREATER,$data['operands'][0]['operator']);
        $this->tester->assertEquals([$column1],$data['operands'][0]['operands']);
    }

    /**
     * @depends testLogicalOperators
     */
    public function testInvalidLogicalOperators() {

        foreach (Condition::$logicalOperators as $operator) {
            $expression = [
                mb_strtolower($operator)
            ];

            $this->tester->expectThrowable(Feather\Exceptions\InvalidConditionException::class,function() use ($expression) {
                Condition::analyze($expression);
            });
        }
    }

    /**
     * @depends testLogicalOperators
     */
    public function testComplexLogicalOperator() {
        $column1 = $this->faker->lexify('column???');
        $column2 = $this->faker->lexify('column???');
        $column3 = $this->faker->lexify('column???');
        $value1 = rand(1,10);
        $value2 = 'my name';
        $value3 = rand(1,10);


        $firstOperator = Condition::OP_OR;
        $secondOperator = Condition::OP_AND;
        

        $expression = [
            $firstOperator => [
                ['vip[is]'=>true],
                $secondOperator=>[
                    [$column1.'[!=]'=>$value1],
                    [$column2.'[like]'=>$value2],
                ]
            ]
        ];

        $data = Condition::analyze($expression);


        $this->tester->assertTrue($data['hasChilds']);
        $this->tester->assertEquals(2,$data['operandsCount']);
        $this->tester->assertEquals($firstOperator,$data['operator']);
        $this->tester->assertEquals(1,$data['operands'][0]['operandsCount']);
        $this->tester->assertTrue($data['operands'][0]['hasValue']);
        $this->tester->assertEquals(Condition::OP_IS,$data['operands'][0]['operator']);
        $this->tester->assertEquals(true,$data['operands'][0]['value'][0]);
        $this->tester->assertFalse($data['operands'][0]['hasChilds']);

        $this->tester->assertEquals($secondOperator,$data['operands'][1]['operator']);
        $this->tester->assertEquals(2,$data['operands'][1]['operandsCount']);
        $this->tester->assertTrue($data['operands'][1]['operands'][0]['hasValue']);
        $this->tester->assertTrue($data['operands'][1]['operands'][1]['hasValue']);
        $this->tester->assertEquals($value1,$data['operands'][1]['operands'][0]['value'][0]);
        $this->tester->assertEquals('%my%name%',$data['operands'][1]['operands'][1]['value'][0]);
        
    }

}