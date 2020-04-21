<?php

namespace Feather\Exceptions;

class InvalidConditionException extends DbException {
    public function __construct($condition = null) {
        parent::__construct('Invalid condition '.serialize($condition));
    }
}