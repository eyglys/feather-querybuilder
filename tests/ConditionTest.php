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
    public function testIsNotWrong() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[!is]'=>null];

        $this->tester->expectException(Feather\Exceptions\InvalidConditionException::class,function() use ($expression) {
            Condition::analyze($expression);
        });

    }
}