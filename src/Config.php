<?php

declare(strict_types=1);

namespace Stfn\CircuitBreaker;

class Config
{
    /**
     * @var float
     */
    public float $failureRatio;

    /**
     * @var int
     */
    public int $minimumThroughput;

    /**
     * @var int
     */
    public int $recoveryTime;

    /**
     * @var int
     */
    public int $sampleDuration;

    /**
     * @param float $failureRatio
     * @param int $minimumThroughput
     * @param int $recoveryTime
     * @param int $sampleDuration
     */
    public function __construct(
        float $failureRatio = 0.5,
        int $minimumThroughput = 10,
        int $recoveryTime = 60,
        int $sampleDuration = 120
    ) {
        if ($failureRatio < 0 || $failureRatio > 1) {
            throw new \InvalidArgumentException("Failure ratio must be between 0 and 1");
        }

        $this->failureRatio = $failureRatio;
        $this->minimumThroughput = $minimumThroughput;
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
            $config['failure_ratio'] ?? 0.5,
            $config['minimum_throughput'] ?? 10,
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
            'failure_ratio' => $this->failureRatio,
            'minimum_throughput' => $this->minimumThroughput,
            'recovery_time' => $this->recoveryTime,
            'sample_duration' => $this->sampleDuration,
        ];
    }
}
