<?php

declare(strict_types=1);

namespace Stfn\CircuitBreaker;

class Config
{
    /**
     * @var float
     */
    public float $failureThreshold;

    /**
     * @var int
     */
    public int $recoveryTime;

    /**
     * @var int
     */
    public int $sampleDuration;

    /**
     * @param float $failureThreshold
     * @param int $recoveryTime
     * @param int $sampleDuration
     */
    public function __construct(
        float $failureThreshold = 5,
        int $recoveryTime = 60,
        int $sampleDuration = 120
    ) {
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTime = $recoveryTime;
        $this->sampleDuration = $sampleDuration;
    }

    /**
     * @param array $config
     * @return Config
     */
    public static function fromArray(array $config = []): Config
    {
        return new Config(
            $config['failure_threshold'] ?? 5,
            $config['recovery_time'] ?? 60,
            $config['sample_duration'] ?? 120
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'failure_ratio' => $this->failureThreshold,
            'recovery_time' => $this->recoveryTime,
            'sample_duration' => $this->sampleDuration,
        ];
    }
}
