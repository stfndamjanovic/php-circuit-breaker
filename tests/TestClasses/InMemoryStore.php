<?php

namespace Stfn\CircuitBreaker\Tests\TestClasses;

use Carbon\Carbon;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;
use Stfn\CircuitBreaker\Stores\IStoreProvider;

class InMemoryStore implements IStoreProvider
{
    public CircuitState $state = CircuitState::Closed;

    protected string|null $lastChangedDateUtc = null;

    protected Counter $counter;

    public function __construct()
    {
        $this->counter = new Counter();
    }

    public function state($service): CircuitState
    {
        return $this->state;
    }

    public function lastChangedDateUtc($service)
    {
        return $this->lastChangedDateUtc;
    }

    public function halfOpen($service): void
    {
        $this->state = CircuitState::HalfOpen;
    }

    public function open($service): void
    {
        $this->state = CircuitState::Open;

        $this->lastChangedDateUtc = Carbon::now("UTC")->toDateTimeString();
    }

    public function close($service): void
    {
        $this->state = CircuitState::Closed;

        $this->lastChangedDateUtc = Carbon::now("UTC")->toDateTimeString();
    }

    public function counter($service): Counter
    {
        return $this->counter;
    }

    public function reset($service)
    {
        $this->counter = new Counter();
    }

    public function onSuccess($result, $service)
    {
        $this->counter->success();
    }

    public function incrementFailure(\Exception $exception, $service)
    {
        $this->counter->failure();
    }
}
