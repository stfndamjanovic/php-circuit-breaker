<?php

declare(strict_types=1);

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\Utilities\Str;

class Config
{
    public string $service;

    public int $maxNumberOfFailures = 1;

    public int $openToHalfOpenWaitTime = 60;

    public int $numberOfSuccessToCloseState = 60;

    public float $percentOfFailures = 0;

    public function __construct(string $service)
    {
        $this->service = $service;
    }

    public function getServiceName(): string
    {
        return $this->service;
    }

    public function setMaxNumberOfFailures(int $maxNumberOfFailures): static
    {
        $this->maxNumberOfFailures = $maxNumberOfFailures;

        return $this;
    }

    public function setOpenToHalfOpenWaitTime(int $openToHalfOpenWaitTime): static
    {
        $this->openToHalfOpenWaitTime = $openToHalfOpenWaitTime;

        return $this;
    }

    public function setNumberOfSuccessToCloseState(int $numberOfSuccessToCloseState): static
    {
        $this->numberOfSuccessToCloseState = $numberOfSuccessToCloseState;

        return $this;
    }

    public function setPercentOfFailures(float $percent): void
    {
        $this->percentOfFailures = $percent;
    }

    public static function make(string $service, array $config = []): Config
    {
        $object = new self($service);

        foreach ($config as $property => $value) {
            $property = Str::camelize($property);
            if (property_exists($object, $property)) {
                $object->{$property} = $value;
            }
        }

        return $object;
    }
}
