<?php

namespace Stfn\CircuitBreaker\Storage;

use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Counter;

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
     * @var int
     */
    protected int $successCount = 0;

    /**
     * @var int|null
     */
    protected null|int $openedAt = null;

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

        $this->failCount = 0;
        $this->successCount = 0;
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
    public function incrementSuccess(): void
    {
        $this->successCount++;
    }

    /**
     * @return Counter
     */
    public function getCounter(): Counter
    {
        return new Counter($this->successCount, $this->failCount);
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
        $this->setState(CircuitState::Open);

        $this->openedAt = time();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->setState(CircuitState::Closed);

        $this->openedAt = null;
    }
}
