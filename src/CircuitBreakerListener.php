<?php

namespace Stfn\CircuitBreaker;

class CircuitBreakerListener
{
    public function beforeCall(\Closure $action, ...$args): void
    {

    }

    public function onSuccess($result): void
    {

    }

    public function onFail($exception): void
    {

    }
}
