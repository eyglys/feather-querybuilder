<?php 
use Feather\Query\Condition;
use Faker\Factory;
use Feather\Exceptions\InvalidConditionException;
class ConditionTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $faker;

    
    protected function _before()
    {
        $this->faker = Factory::create();
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

            $this->tester->assertTrue($data['columnsCount'] == 1);
            $this->tester->assertTrue($data['columns'][0] == $column);
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

            $this->tester->assertTrue($data['columnsCount'] == 2);
            $this->tester->assertTrue($data['columns'][0] == $column1);
            $this->tester->assertTrue($data['columns'][1] == $column2);
            $this->tester->assertTrue($data['operator'] == $operator);
        }
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testFullLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like]'=>'teste1 teste2'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%teste1%teste2%',$data['value'][0]);
    }
    /**
     * @depends testBasicOneColumnOperator
     */
    public function testBothLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like%]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%teste%',$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testLeftLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('%teste',$data['value'][0]);
    }

    /**
     * @depends testBasicOneColumnOperator
     */
    public function testRightLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like%]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->tester->assertTrue($data['hasValue']);
        $this->tester->assertEquals('teste%',$data['value'][0]);
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
        $this->tester->assertEquals(3,$data['columnsCount']);
        $this->tester->assertEquals([$column1,$column2,$column3],$data['columns']);
    }


    
}