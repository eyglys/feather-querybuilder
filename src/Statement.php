<?php
declare(strict_types=1);
namespace Feather;

abstract class Statement extends Base {
    /**
     * @return bool True if a minimal elements has configured
     */
    abstract public function isBuildable();
}