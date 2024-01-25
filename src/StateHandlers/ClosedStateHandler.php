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
        $storage = $this->breaker->getStorage();

        $storage->incrementFailure();

        $config = $this->breaker->getConfig();

        if ($storage->getCounter()->numberOfFailures() >= $config->failureThreshold) {
            $this->breaker->openCircuit();

            throw CircuitOpenException::make($this->breaker->getName());
        }
    }
}
