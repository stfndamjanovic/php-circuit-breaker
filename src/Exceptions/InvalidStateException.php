<?php

namespace Stfn\CircuitBreaker\Exceptions;

class InvalidStateException extends \Exception
{
    /**
     * @param $state
     * @return InvalidStateException
     */
    public static function make($state)
    {
        return new self("State {$state} is not valid.");
    }
}
