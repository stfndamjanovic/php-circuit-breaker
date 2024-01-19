<?php

namespace Stfn\CircuitBreaker\Exceptions;

class CircuitForceOpenException extends \Exception
{
    public static function make()
    {
        return new self("The circuit is manually opened.");
    }
}
