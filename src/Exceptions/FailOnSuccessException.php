<?php

namespace Stfn\CircuitBreaker\Exceptions;

class FailOnSuccessException extends \Exception
{
    public static function make()
    {
        return new self("The circuit is manually failed");
    }
}
