<?php

namespace Stfn\CircuitBreaker;

class Counter
{
    protected int $numberOfFailures = 0;
    protected int $numberOfSuccess = 0;

    public function getNumberOfFailures()
    {
        return $this->numberOfFailures;
    }

    public function getNumberOfSuccess()
    {
        return $this->numberOfSuccess;
    }

    public function success()
    {
        $this->numberOfSuccess++;
    }

    public function failure()
    {
        $this->numberOfFailures++;
    }

    public function failurePercent()
    {
        return round($this->numberOfFailures / $this->totalTries(), 2);
    }

    public function totalTries()
    {
        return $this->numberOfSuccess + $this->numberOfFailures;
    }
}
