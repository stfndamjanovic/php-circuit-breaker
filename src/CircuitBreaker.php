<?php

namespace Stfn\CircuitBreaker;

use Carbon\Carbon;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;
use Stfn\CircuitBreaker\Stores\IStoreProvider;

class CircuitBreaker
{
    protected Config $config;

    protected IStoreProvider $store;

    public function __construct(Config $config, IStoreProvider $store)
    {
        $this->config = $config;
        $this->store = $store;
    }

    public function run(\Closure $action)
    {
        if ($this->isOpen()) {
            if (! $this->shouldBecomeHalfOpen()) {
                throw CircuitOpenException::make($this->config->getServiceName());
            }

            try {
                $this->store->halfOpen();

                $result = call_user_func($action);

                $this->store->onSuccess($result);
            } catch (\Exception $exception) {
                $this->openCircuit();

                throw $exception;
            }

            if ($this->store->counter()->getNumberOfSuccess() >= $this->config->numberOfSuccessToCloseState) {
                $this->store->close();
            }

            return $result;
        }

        try {
            $result = call_user_func($action);

            $this->store->onSuccess($result);
        } catch (\Exception $exception) {
            $this->handleFailure($exception);

            throw $exception;
        }

        return $result;
    }

    public function isOpen()
    {
        return $this->store->state() != CircuitState::Closed;
    }

    public function shouldBecomeHalfOpen()
    {
        $lastChange = $this->store->lastChangedDateUtc();

        if ($lastChange) {
            $now = Carbon::now("UTC");

            $shouldBeHalfOpenAt = Carbon::parse($this->store->lastChangedDateUtc())
                ->timezone("UTC")
                ->addSeconds($this->config->openToHalfOpenWaitTime);

            return $shouldBeHalfOpenAt > $now;
        }

        return false;
    }

    public function handleFailure(\Exception $exception)
    {
        // Log exception

        $this->store->incrementFailure($exception);

        // Open circuit if needed
        if ($this->store->counter()->getNumberOfFailures() > $this->config->maxNumberOfFailures) {
            $this->openCircuit();
        }
    }

    public function openCircuit()
    {
        $this->store->open();
        $this->store->reset();
    }
}
