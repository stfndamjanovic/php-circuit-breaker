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
        $failures = $this->breaker->getStorage()->getCounter()->numberOfFailures();

        $threshold = $this->breaker->getConfig()->failureThreshold;

        if ($failures >= $threshold) {
            $this->breaker->openCircuit();

            throw CircuitOpenException::make($this->breaker->getName());
        }
    }
}
