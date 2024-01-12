<?php

namespace Stfn\CircuitBreaker\Exceptions;

class FailOnSuccessException extends \Exception
{
    public static function make()
    {
        return new self("Circuit breaker failed on success.");
    }
}
