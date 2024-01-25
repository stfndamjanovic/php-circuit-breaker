<?php

declare(strict_types=1);

namespace Stfn\CircuitBreaker;

class Config
{
    /**
     * @var int
     */
    public int $failureThreshold;

    /**
     * @var int
     */
    public int $recoveryTime;

    /**
     * @var int
     */
    public int $sampleDuration;

    /**
     * @var int
     */
    public int $consecutiveSuccess;

    /**
     * @param int $failureThreshold
     * @param int $recoveryTime
     * @param int $sampleDuration
     * @param int $consecutiveSuccesses
     */
    public function __construct(
        int $failureThreshold = 5,
        int $recoveryTime = 60,
        int $sampleDuration = 120,
        int $consecutiveSuccess = 3
    ) {
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTime = $recoveryTime;
        $this->sampleDuration = $sampleDuration;
        $this->consecutiveSuccess = $consecutiveSuccess;
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
            $config['sample_duration'] ?? 120,
            $config['consecutive_success'] ?? 3
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'failure_threshold' => $this->failureThreshold,
            'recovery_time' => $this->recoveryTime,
            'sample_duration' => $this->sampleDuration,
            'consecutive_success' => $this->consecutiveSuccess,
        ];
    }
}
