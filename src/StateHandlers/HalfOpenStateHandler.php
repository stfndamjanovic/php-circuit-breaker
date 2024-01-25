<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\Exceptions\CircuitHalfOpenFailException;

class HalfOpenStateHandler extends StateHandler
{
    /**
     * @return void
     */
    public function onSucess()
    {
        $storage = $this->breaker->getStorage();

        $storage->incrementSuccess();

        if ($storage->getCounter()->numberOfSuccess() >= $this->breaker->getConfig()->consecutiveSuccess) {
            $this->breaker->closeCircuit();
        }
    }

    /**
     * @param \Exception $exception
     * @return void
     * @throws CircuitHalfOpenFailException
     */
    public function onFailure(\Exception $exception)
    {
        $this->breaker->openCircuit();

        throw CircuitHalfOpenFailException::make();
    }
}
