<?php

namespace Stfn\CircuitBreaker;

class Counter
{
    protected int $numberOfFailures = 0;
    protected int $numberOfSuccess = 0;

    public function getNumberOfFailures(): int
    {
        return $this->numberOfFailures;
    }

    public function getNumberOfSuccess(): int
    {
        return $this->numberOfSuccess;
    }

    public function success(): void
    {
        $this->numberOfSuccess++;
    }

    public function failure(): void
    {
        $this->numberOfFailures++;
    }

    public function reset()
    {
        $this->numberOfFailures = 0;
        $this->numberOfSuccess = 0;
    }

    public function failurePercent(): float
    {
        return round($this->numberOfFailures / $this->totalTries(), 2);
    }

    public function totalTries(): int
    {
        return $this->numberOfSuccess + $this->numberOfFailures;
    }
}
