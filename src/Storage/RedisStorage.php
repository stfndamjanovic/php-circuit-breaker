<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitState;

class RedisStorage extends CircuitBreakerStorage
{
    public const BASE_NAMESPACE = "stfn_php_circuit_breaker";
    public const STATE_KEY = "state";
    public const FAIL_COUNT_KEY = "fail_count";
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
    public function __construct(string $service, \Redis $redis)
    {
        parent::__construct($service);

        $this->redis = $redis;

        $this->initState();
    }

    /**
     * @return void
     * @throws \RedisException
     */
    protected function initState()
    {
        $this->redis->setnx($this->getNamespace(self::STATE_KEY), CircuitState::Closed->value);
        $this->redis->setnx($this->getNamespace(self::FAIL_COUNT_KEY), 0);
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
        $this->redis->incr($this->getNamespace(self::FAIL_COUNT_KEY));
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function resetCounter(): void
    {
        $this->redis->set($this->getNamespace(self::FAIL_COUNT_KEY), 0);
    }

    /**
     * @return int
     * @throws \RedisException
     */
    public function getFailuresCount(): int
    {
        return (int) $this->redis->get($this->getNamespace(self::FAIL_COUNT_KEY));
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
     * @param string $key
     * @return string
     */
    protected function getNamespace(string $key): string
    {
        $tags = [self::BASE_NAMESPACE, $this->service, $key];

        return join(":", $tags);
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
}
