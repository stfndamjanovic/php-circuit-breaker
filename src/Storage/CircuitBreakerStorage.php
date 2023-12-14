<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitState;

abstract class CircuitBreakerStorage
{
    /**
     * @var string
     */
    protected string $service;

    /**
     * @param string $service
     */
    public function __construct(string $service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
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
    abstract public function resetCounter(): void;

    /**
     * @return int
     */
    abstract public function getFailuresCount(): int;

    /**
     * @return int
     */
    abstract public function openedAt(): int;
}
