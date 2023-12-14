<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;

class ClosedStateHandler extends StateHandler
{
    /**
     * @param \Exception $exception
     * @return void
     * @throws CircuitOpenException
     */
    public function onFailure(\Exception $exception)
    {
        $storage = $this->breaker->storage;

        $storage->incrementFailure();

        $failuresCount = $storage->getFailuresCount();

        if ($failuresCount >= $this->breaker->config->failureThreshold) {
            $this->breaker->openCircuit();

            throw CircuitOpenException::make($storage->getService());
        }
    }
}
