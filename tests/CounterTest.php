<?php

namespace Stfn\CircuitBreaker\Tests;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\Counter;

class CounterTest extends TestCase
{
    public function test_if_it_can_calculate_total_number_of_requests()
    {
        $counter = new Counter(2, 3);

        $this->assertEquals(5, $counter->total());
    }

    public function test_if_it_can_calculate_failure_ratio()
    {
        $counter = new Counter(2, 8);

        $this->assertEquals(0.2, $counter->failureRatio());
    }
}
