<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitState;

class InMemoryStorage extends CircuitBreakerStorage
{
    /**
     * @var CircuitState
     */
    public CircuitState $state = CircuitState::Closed;

    /**
     * @var int
     */
    protected int $failCount = 0;

    /**
     * @var int|null
     */
    protected null|int $openedAt;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct("in_memory");

        $this->openedAt = null;
    }

    /**
     * @return CircuitState
     */
    public function getState(): CircuitState
    {
        return $this->state;
    }

    /**
     * @param CircuitState $state
     * @return void
     */
    public function setState(CircuitState $state): void
    {
        $this->state = $state;
    }

    /**
     * @return void
     */
    public function incrementFailure(): void
    {
        $this->failCount++;
    }

    /**
     * @return void
     */
    public function resetCounter(): void
    {
        $this->failCount = 0;
    }

    /**
     * @return int
     */
    public function getFailuresCount(): int
    {
        return $this->failCount;
    }

    /**
     * @return int
     */
    public function openedAt(): int
    {
        return $this->openedAt;
    }

    /**
     * @return void
     */
    public function open(): void
    {
        $this->state = CircuitState::Open;

        $this->openedAt = time();

        $this->resetCounter();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->state = CircuitState::Closed;

        $this->openedAt = null;
    }
}
