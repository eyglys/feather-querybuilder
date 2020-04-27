<?php


namespace Feather;

/**
 * Base class
 */
class Base {

    public $log;
    /**
     * Automatically sets properties
     */
    public function __construct(array $params = []) {
        foreach ($params as $attribute => $value) {
            if (property_exists($this,$attribute)) {
                $this->$attribute = $value;
            }
        }
    }
}