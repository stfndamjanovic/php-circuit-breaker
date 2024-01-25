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
     * @param int $success
     * @param int $failures
     */
    public function __construct(int $success, int $failures)
    {
        $this->success = $success;
        $this->failures = $failures;
    }

    /**
     * @return int
     */
    public function numberOfSuccess()
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function numberOfFailures()
    {
        return $this->failures;
    }
}
