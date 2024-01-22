<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;

class ClosedStateHandler extends StateHandler
{
    /**
     * @return void
     */
    public function onSucess()
    {
        $this->breaker->getStorage()->incrementSuccess();
    }

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

        $counter = $storage->getCounter();

        if ($counter->total() >= $config->minimumThroughput && $counter->failureRatio() >= $config->failureRatio) {
            $this->breaker->openCircuit();

            throw CircuitOpenException::make($this->breaker->getName());
        }
    }
}
