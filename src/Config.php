<?php

namespace Stfn\CircuitBreaker;

class Config
{
    public string $service;

    public int $maxNumberOfFailures = 1;

    public int $openToHalfOpenWaitTime = 60;

    public int $numberOfSuccessToCloseState = 60;

    public float $percentOfFailures = 0;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function getServiceName()
    {
        return $this->service;
    }

    public function setMaxNumberOfFailures($maxNumberOfFailures)
    {
        $this->maxNumberOfFailures = $maxNumberOfFailures;

        return $this;
    }

    public function setOpenToHalfOpenWaitTime($openToHalfOpenWaitTime)
    {
        $this->openToHalfOpenWaitTime = $openToHalfOpenWaitTime;

        return $this;
    }

    public function setNumberOfSuccessToCloseState($numberOfSuccessToCloseState)
    {
        $this->numberOfSuccessToCloseState = $numberOfSuccessToCloseState;

        return $this;
    }

    public function setPercentOfFailures(float $percent)
    {
        $this->percentOfFailures = $percent;
    }
}
