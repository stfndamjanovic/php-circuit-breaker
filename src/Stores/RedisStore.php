<?php

namespace Stfn\CircuitBreaker\Stores;

use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

class RedisStore implements IStoreProvider
{
    protected \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function state($service): CircuitState
    {
        $key = $this->getStateKey($service);

        $state = $this->redis->get($key);

        if (! $state) {
            return CircuitState::Closed;
        }

        return CircuitState::from($state);
    }

    public function lastChangedDateUtc($service)
    {
        // TODO: Implement lastChangedDateUtc() method.
    }

    public function halfOpen($service): void
    {
        $this->redis->set($this->getStateKey($service), CircuitState::HalfOpen->value, 1000);
    }

    public function open($service): void
    {
        // ToDo Define timeouts
        $this->redis->set($this->getStateKey($service), CircuitState::Open->value, 1000);
    }

    public function close($service): void
    {
        $this->redis->set($this->getStateKey($service), CircuitState::Closed->value, 1000);
    }

    public function counter($service): Counter
    {
        $key = $this->getCounterKey($service);

        $counter = $this->redis->get($key);

        if (! $counter) {
            return new Counter();
        }

        return unserialize($counter);
    }

    public function reset($service)
    {
        $counter = $this->counter($service);

        $counter->reset();

        $this->redis->set($this->getCounterKey($service), serialize($counter), 1000);
    }

    public function onSuccess($result, $service)
    {
        $counter = $this->counter($service);

        $counter->success();

        $this->redis->set($this->getCounterKey($service), serialize($counter), 1000);
    }

    public function incrementFailure(\Exception $exception, $service)
    {
        $counter = $this->counter($service);

        $counter->failure();

        $this->redis->set($this->getCounterKey($service), serialize($counter), 1000);
    }

    protected function getCounterKey($service)
    {
        return $this->getKey($service) . ':counter';
    }

    protected function getStateKey($service)
    {
        return $this->getKey($service) . ":state";
    }

    protected function getKey($service)
    {
        return "stfn-circuit-breaker-package:{$service}";
    }
}
