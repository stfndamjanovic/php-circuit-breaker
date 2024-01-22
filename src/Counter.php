<?php

namespace Stfn\CircuitBreaker;

class Counter
{
    /**
     * @var int
     */
    protected int $success;

    /**
     * @var int
     */
    protected int $failures;

    /**
     * @param $failures
     * @param $success
     */
    public function __construct($failures, $success)
    {
        $this->failures = $failures;
        $this->success = $success;
    }

    /**
     * @return int
     */
    public function getNumberOfSuccess()
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getNumberOfFailures()
    {
        return $this->failures;
    }

    /**
     * @return float|int
     */
    public function failureRatio()
    {
        return $this->failures / $this->total();
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->failures + $this->success;
    }
}
