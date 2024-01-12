<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;

class OpenStateHandler extends StateHandler
{
    /**
     * @param \Closure $action
     * @param ...$args
     * @return void
     * @throws CircuitOpenException
     */
    public function beforeCall(\Closure $action, ...$args)
    {
        $storage = $this->breaker->storage;

        $openedAt = $storage->openedAt();

        $recoveryTime = $this->breaker->config->recoveryTime;

        if ($openedAt && (time() - $openedAt) > $recoveryTime) {
            $storage->setState(CircuitState::HalfOpen);

            return;
        }

        throw CircuitOpenException::make($this->breaker->name);
    }
}
