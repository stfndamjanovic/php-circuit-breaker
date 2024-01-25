<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

abstract class CircuitBreakerStorage
{
    /**
     * @var CircuitBreaker
     */
    protected CircuitBreaker $breaker;

    /**
     * @param CircuitBreaker $breaker
     * @return void
     */
    public function init(CircuitBreaker $breaker): void
    {
        $this->breaker = $breaker;
    }

    /**
     * @return CircuitState
     */
    abstract public function getState(): CircuitState;

    /**
     * @param CircuitState $state
     * @return void
     */
    abstract public function setState(CircuitState $state): void;

    /**
     * @return void
     */
    abstract public function open(): void;

    /**
     * @return void
     */
    abstract public function close(): void;

    /**
     * @return void
     */
    abstract public function incrementFailure(): void;

    /**
     * @return void
     */
    abstract public function incrementSuccess(): void;

    /**
     * @return Counter
     */
    abstract public function getCounter(): Counter;

    /**
     * @return int
     */
    abstract public function openedAt(): int;
}
