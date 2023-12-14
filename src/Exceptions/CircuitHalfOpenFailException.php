<?php

namespace Stfn\CircuitBreaker\Exceptions;

class CircuitHalfOpenFailException extends \Exception
{
    /**
     * @return CircuitHalfOpenFailException
     */
    public static function make()
    {
        return new self("Circuit failed in half open state");
    }
}
