<?php

namespace Stfn\CircuitBreaker;

class CircuitBreakerListener
{
    public function beforeCall(CircuitBreaker $breaker, \Closure $action, ...$args): void
    {

    }

    public function onSuccess(CircuitBreaker $breaker, $result): void
    {

    }

    public function onFail(CircuitBreaker $breaker, $exception): void
    {

    }

    public function onStateChange(CircuitBreaker $breaker, CircuitState $previousState, CircuitState $newState): void
    {

    }
}
