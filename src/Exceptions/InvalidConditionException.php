<?php

namespace Feather\Exceptions;

class InvalidConditionException extends DbException {
    public function __construct($condition = null) {
        $message = '';
        if (is_string($condition)) {
            $message = $condition;
        } else $message = serialize($condition);
        parent::__construct('Invalid condition: '.$message);
    }
}