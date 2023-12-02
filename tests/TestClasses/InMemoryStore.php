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

    public function state(): CircuitState
    {
        return $this->state;
    }

    public function lastChangedDateUtc()
    {
        return $this->lastChangedDateUtc;
    }

    public function halfOpen(): void
    {
        $this->state = CircuitState::HalfOpen;
    }

    public function open(): void
    {
        $this->state = CircuitState::Open;

        $this->lastChangedDateUtc = Carbon::now("UTC")->toDateTimeString();
    }

    public function close(): void
    {
        $this->state = CircuitState::Closed;

        $this->lastChangedDateUtc = Carbon::now("UTC")->toDateTimeString();
    }

    public function counter(): Counter
    {
        return $this->counter;
    }

    public function reset()
    {
        $this->counter = new Counter();
    }

    public function onSuccess($result)
    {
        $this->counter->success();
    }

    public function incrementFailure(\Exception $exception)
    {
        $this->counter->failure();
    }
}
