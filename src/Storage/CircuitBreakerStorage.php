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
    public abstract function getState(): CircuitState;

    /**
     * @param CircuitState $state
     * @return void
     */
    public abstract function setState(CircuitState $state): void;

    /**
     * @return void
     */
    public abstract function open(): void;

    /**
     * @return void
     */
    public abstract function close(): void;

    /**
     * @return void
     */
    public abstract function incrementFailure(): void;

    /**
     * @return void
     */
    public abstract function resetCounter(): void;

    /**
     * @return int
     */
    public abstract function getFailuresCount(): int;

    /**
     * @return int
     */
    public abstract function openedAt(): int;
}
