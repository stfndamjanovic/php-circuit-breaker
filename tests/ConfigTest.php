<?php

namespace Stfn\CircuitBreaker\Tests;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\Config;

class ConfigTest extends TestCase
{
    public function test_if_it_will_throw_an_exception_on_invalid_failure_ratio()
    {
        $this->expectException(\TypeError::class);

        Config::fromArray(['failure_threshold' => "1"]);
    }

    public function test_if_it_can_set_valid_config()
    {
        $setup = [
            'failure_threshold' => 20,
            'recovery_time' => 200,
            'sample_duration' => 20,
        ];

        $config = Config::fromArray($setup);

        $this->assertEquals($setup['failure_threshold'], $config->failureThreshold);
        $this->assertEquals($setup['recovery_time'], $config->recoveryTime);
        $this->assertEquals($setup['sample_duration'], $config->sampleDuration);
    }
}
