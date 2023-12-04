<?php

namespace Stfn\CircuitBreaker;

use Carbon\Carbon;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;
use Stfn\CircuitBreaker\Stores\IStoreProvider;

class CircuitBreaker
{
    protected Config $config;

    protected IStoreProvider $store;

    protected string $service;

    public function __construct(Config $config, IStoreProvider $store)
    {
        $this->config = $config;
        $this->store = $store;
        $this->service = $config->getServiceName();
    }

    public function run(\Closure $action)
    {
        if ($this->isOpen()) {
            if (! $this->shouldBecomeHalfOpen()) {
                throw CircuitOpenException::make($this->service);
            }

            try {
                $this->store->halfOpen($this->service);

                $result = call_user_func($action);

                $this->store->onSuccess($result, $this->service);
            } catch (\Exception $exception) {
                $this->openCircuit();

                throw $exception;
            }

            if ($this->store->counter($this->service)->getNumberOfSuccess() >= $this->config->numberOfSuccessToCloseState) {
                $this->store->close($this->service);
            }

            return $result;
        }

        try {
            $result = call_user_func($action);

            $this->store->onSuccess($result, $this->service);
        } catch (\Exception $exception) {
            $this->handleFailure($exception);

            throw $exception;
        }

        return $result;
    }

    public function isOpen()
    {
        return $this->store->state($this->service) != CircuitState::Closed;
    }

    public function shouldBecomeHalfOpen(): bool
    {
        $lastChange = $this->store->lastChangedDateUtc($this->service);

        if ($lastChange) {
            $now = Carbon::now("UTC");

            $shouldBeHalfOpenAt = Carbon::parse($lastChange)
                ->timezone("UTC")
                ->addSeconds($this->config->openToHalfOpenWaitTime);

            return $shouldBeHalfOpenAt > $now;
        }

        return false;
    }

    public function handleFailure(\Exception $exception): void
    {
        // Log exception

        $this->store->incrementFailure($exception, $this->service);

        // Open circuit if needed
        if ($this->store->counter($this->service)->getNumberOfFailures() > $this->config->maxNumberOfFailures) {
            $this->openCircuit();
        }
    }

    public function openCircuit(): void
    {
        $this->store->open($this->service);
        $this->store->reset($this->service);
    }
}
