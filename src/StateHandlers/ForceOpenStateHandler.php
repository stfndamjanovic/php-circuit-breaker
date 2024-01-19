<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\Exceptions\CircuitForceOpenException;

class ForceOpenStateHandler extends StateHandler
{
    /**
     * @param \Closure $action
     * @param ...$args
     * @return void
     * @throws CircuitForceOpenException
     */
    public function beforeCall(\Closure $action, ...$args)
    {
        throw CircuitForceOpenException::make();
    }
}
