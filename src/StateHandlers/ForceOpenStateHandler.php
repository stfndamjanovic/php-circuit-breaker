<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;

class ForceOpenStateHandler extends StateHandler
{
    /**
     * @param \Closure $action
     * @param ...$args
     * @return void
     * @throws CircuitOpenException
     */
    public function beforeCall(\Closure $action, ...$args)
    {
        throw CircuitOpenException::make($this->breaker->getName());
    }
}
