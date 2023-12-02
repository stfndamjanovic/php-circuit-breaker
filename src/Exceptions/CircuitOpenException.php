<?php

namespace Stfn\CircuitBreaker\Exceptions;

class CircuitOpenException extends \Exception
{
    public static function make($service)
    {
        return new self("Circuit breaker for service '{$service}' is open");
    }
}
