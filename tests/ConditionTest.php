<?php 
use Feather\Query\Condition;
use Faker\Factory;
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


    
    public function testOneColumnOperator()
    {
        foreach (Condition::$relationalOperators as $operator) {
            $column = $this->faker->lexify('column???');
            $expression = $column.'['.$operator.']';

            $data = Condition::analyze($expression);

            $this->assertTrue($data['columnsCount'] == 1);
            $this->assertTrue($data['columns'][0] == $column);
            $this->assertTrue($data['operator'] == $operator);
        }
    }

    /**
     * @depends testOneColumnOperator
     */
    public function testTwoColumnsOperator()
    {
        foreach (Condition::$relationalOperators as $operator) {
            $column1 = $this->faker->lexify('column???');
            $column2 = $this->faker->lexify('column???');
            $expression = $column1.'['.$operator.']'.$column2;

            $data = Condition::analyze($expression);

            $this->assertTrue($data['columnsCount'] == 2);
            $this->assertTrue($data['columns'][0] == $column1);
            $this->assertTrue($data['columns'][1] == $column2);
            $this->assertTrue($data['operator'] == $operator);
        }
    }

    /**
     * @depends testOneColumnOperator
     */
    public function testFullLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like]'=>'teste1 teste2'];

        $data = Condition::analyze($expression);
        $this->assertTrue($data['hasValue']);
        $this->assertEquals('%teste1%teste2%',$data['value'][0]);
    }
    /**
     * @depends testOneColumnOperator
     */
    public function testBothLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like%]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->assertTrue($data['hasValue']);
        $this->assertEquals('%teste%',$data['value'][0]);
    }

    /**
     * @depends testOneColumnOperator
     */
    public function testLeftLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[%like]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->assertTrue($data['hasValue']);
        $this->assertEquals('%teste',$data['value'][0]);
    }

    /**
     * @depends testOneColumnOperator
     */
    public function testRightLike() {
        $column1 = $this->faker->lexify('column???');
        $expression = [$column1.'[like%]'=>'teste'];

        $data = Condition::analyze($expression);
        $this->assertTrue($data['hasValue']);
        $this->assertEquals('teste%',$data['value'][0]);
    }
}