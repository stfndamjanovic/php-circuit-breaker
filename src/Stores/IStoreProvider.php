<?php

namespace Stfn\CircuitBreaker\Stores;

use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

interface IStoreProvider
{
    public function state(): CircuitState;

    public function lastChangedDateUtc();

    public function halfOpen(): void;

    public function open(): void;

    public function close(): void;

    public function counter(): Counter;

    public function reset();

    public function onSuccess($result);

    public function incrementFailure(\Exception $exception);
}
