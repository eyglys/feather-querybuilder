<?php
declare(strict_types=1);
namespace Feather\Builder;

use \Feather\Statement;

abstract class BaseBuilder extends \Feather\Base {
    /**
     * Driver
     */
    public $driver = null;
    protected $paramBuilder;

}