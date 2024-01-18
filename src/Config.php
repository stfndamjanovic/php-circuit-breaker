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
     * @param int $failureThreshold
     * @param int $recoveryTime
     */
    public function __construct(int $failureThreshold = 5, int $recoveryTime = 60)
    {
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTime = $recoveryTime;
    }

    /**
     * @param array $config
     * @return Config
     */
    public static function fromArray(array $config = []): Config
    {
        return new Config(
            $config['failure_threshold'] ?? 5,
            $config['recovery_time'] ?? 60
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
        ];
    }
}
