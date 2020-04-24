<?php
namespace Feather\Exceptions;

class InvalidValueException extends DbException {
    public function __construct($value) {
        parent::__construct('Invalid value '.serialize($value));
    }
}