<?php

namespace Stfn\CircuitBreaker\Stores;

use Stfn\CircuitBreaker\Counter;
use Stfn\CircuitBreaker\CircuitState;

class RedisStore implements IStoreProvider
{
    protected \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function state(): CircuitState
    {
        return $this->redis->get($this->getNamespace());
    }

    public function lastChangedDateUtc()
    {
        // TODO: Implement lastChangedDateUtc() method.
    }

    public function halfOpen(): void
    {
        $this->redis->set($this->getNamespace(), CircuitState::HalfOpen->value, 1000);
    }

    public function open(): void
    {
        // ToDo Define timeouts
        $this->redis->set($this->getNamespace(), CircuitState::Open->value, 1000);
    }

    public function close(): void
    {
        $this->redis->set($this->getNamespace(), CircuitState::Closed->value, 1000);
    }

    public function counter(): Counter
    {
        // TODO: Implement counter() method.
    }

    public function reset()
    {
        // TODO: Implement reset() method.
    }

    public function onSuccess($result)
    {
        // TODO: Implement onSuccess() method.
    }

    public function incrementFailure(\Exception $exception)
    {
        $failuresKey = $this->getNamespace() . ':failure:counter';

        if (! $this->redis->get($failuresKey)) {
            $this->redis->multi();
            $this->redis->incr($failuresKey);

            return (bool) ($this->redis->exec()[0] ?? false);
        }

        return (bool) $this->redis->incr($failuresKey);
    }

    public function getNamespace()
    {
        return "stfn-circuit-breaker-package:{$this->service}";
    }
}
