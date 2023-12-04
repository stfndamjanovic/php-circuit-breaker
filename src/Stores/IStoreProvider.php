<?php

namespace Stfn\CircuitBreaker\Stores;

use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

interface IStoreProvider
{
    public function state($service): CircuitState;

    public function lastChangedDateUtc($service);

    public function halfOpen($service): void;

    public function open($service): void;

    public function close($service): void;

    public function counter($service): Counter;

    public function reset($service);

    public function onSuccess($result, $service);

    public function incrementFailure(\Exception $exception, $service);
}
