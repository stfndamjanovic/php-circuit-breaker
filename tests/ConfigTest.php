<?php

namespace Stfn\CircuitBreaker\Tests;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\Config;

class ConfigTest extends TestCase
{
    public function test_if_it_will_throw_an_exception_on_invalid_failure_ratio()
    {
        $this->expectException(\InvalidArgumentException::class);

        Config::fromArray(['failure_ratio' => 1.01]);
    }

    public function test_if_it_can_set_valid_config()
    {
        $setup = [
            'failure_ratio' => 0.2,
            'minimum_throughput' => 10,
            'recovery_time' => 120,
            'sample_duration' => 120
        ];

        $config = Config::fromArray($setup);

        $this->assertEquals($setup['failure_ratio'], $config->failureRatio);
        $this->assertEquals($setup['minimum_throughput'], $config->minimumThroughput);
        $this->assertEquals($setup['recovery_time'], $config->recoveryTime);
        $this->assertEquals($setup['sample_duration'], $config->sampleDuration);
    }
}
