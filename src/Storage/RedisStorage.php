<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

class RedisStorage extends CircuitBreakerStorage
{
    public const BASE_NAMESPACE = "stfn_php_circuit_breaker";
    public const STATE_KEY = "state";
    public const FAIL_COUNT_KEY = "fail_count";
    public const SUCCESS_COUNT_KEY = "success_count";
    public const OPENED_AT_KEY = "opened_at";

    /**
     * @var \Redis
     */
    protected \Redis $redis;

    /**
     * @param string $service
     * @param \Redis $redis
     * @throws \RedisException
     */
    public function __construct(\Redis $redis)
    {
        if (! extension_loaded('redis')) {
            throw new \Exception("PHP Redis extension must be loaded.");
        }

        $this->redis = $redis;
    }

    /**
     * @param string $service
     * @return void
     * @throws \RedisException
     */
    public function init(CircuitBreaker $breaker): void
    {
        parent::init($breaker);

        $this->redis->setnx($this->getNamespace(self::STATE_KEY), CircuitState::Closed->value);
    }

    /**
     * @return CircuitState
     * @throws \RedisException
     */
    public function getState(): CircuitState
    {
        $state = $this->redis->get($this->getNamespace(self::STATE_KEY));

        return CircuitState::from($state);
    }

    /**
     * @param CircuitState $state
     * @return void
     * @throws \RedisException
     */
    public function setState(CircuitState $state): void
    {
        $this->redis->set($this->getNamespace(self::STATE_KEY), $state->value);
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function incrementFailure(): void
    {
        $this->incrementOrCreate($this->getNamespace(self::FAIL_COUNT_KEY), $this->breaker->getConfig()->sampleDuration);
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function incrementSuccess(): void
    {
        $this->incrementOrCreate($this->getNamespace(self::SUCCESS_COUNT_KEY), $this->breaker->getConfig()->sampleDuration);
    }

    /**
     * @param $key
     * @param $ttl
     * @return void
     * @throws \RedisException
     */
    protected function incrementOrCreate($key, $ttl)
    {
        if (! $this->redis->exists($key)) {
             $this->redis->set($key, 0, $ttl);
        }

        $this->redis->incr($key);
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function resetCounter(): void
    {
        $this->redis->del($this->getNamespace(self::FAIL_COUNT_KEY));
        $this->redis->del($this->getNamespace(self::SUCCESS_COUNT_KEY));
    }

    /**
     * @return Counter
     * @throws \RedisException
     */
    public function getCounter(): Counter
    {
        $failuresCount = (int) $this->redis->get($this->getNamespace(self::FAIL_COUNT_KEY));
        $successCount = (int) $this->redis->get($this->getNamespace(self::SUCCESS_COUNT_KEY));

        return new Counter($failuresCount, $successCount);
    }

    /**
     * @return int
     * @throws \RedisException
     */
    public function openedAt(): int
    {
        return (int) $this->redis->get($this->getNamespace(self::OPENED_AT_KEY));
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function open(): void
    {
        $this->setState(CircuitState::Open);

        $this->redis->set($this->getNamespace(self::OPENED_AT_KEY), time());

        $this->resetCounter();
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function close(): void
    {
        $this->setState(CircuitState::Closed);

        $this->redis->del($this->getNamespace(self::OPENED_AT_KEY));
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getNamespace(string $key): string
    {
        $tags = [self::BASE_NAMESPACE, $this->breaker->getName(), $key];

        return join(":", $tags);
    }
}
