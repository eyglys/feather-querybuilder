<?php 

use \Feather\Driver\MySQL\MySQL;

class MySQLDriverTest extends \Test\DriverBase
{
    public function __construct() {
        $this->driver = new MySQL();
        parent::__construct();
    }
}