<?php

namespace Stfn\CircuitBreaker\StateHandlers;

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
        $storage = $this->breaker->getStorage();

        $openedAt = $storage->openedAt();

        $recoveryTime = $this->breaker->getConfig()->recoveryTime;

        if ($openedAt && (time() - $openedAt) > $recoveryTime) {
            $this->breaker->halfOpenCircuit();

            return;
        }

        throw CircuitOpenException::make($this->breaker->getName());
    }
}
