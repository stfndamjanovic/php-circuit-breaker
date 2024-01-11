<?php

namespace Stfn\CircuitBreaker\Tests;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitBreakerListener;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Config;
use Stfn\CircuitBreaker\Exceptions\CircuitHalfOpenFailException;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;

class CircuitBreakerTest extends TestCase
{
    public function test_if_it_can_handle_function_success()
    {
        $breaker = new CircuitBreaker();

        $result = $breaker->call(function () {
            return true;
        });

        $this->assertTrue($result);

        $object = new \stdClass();

        $result = $breaker->call(function () use ($object) {
            return $object;
        });

        $this->assertEquals($object, $result);
    }

    public function test_if_it_will_throw_an_exception_if_circuit_breaker_is_open()
    {
        $breaker = new CircuitBreaker();
        $breaker->openCircuit();

        $this->expectException(CircuitOpenException::class);

        $breaker->call(function () {
            return true;
        });
    }

    public function test_if_it_will_record_every_success()
    {
        $breaker = new CircuitBreaker();

        $success = function () {
            return true;
        };

        $tries = 3;

        foreach (range(1, $tries) as $i) {
            $breaker->call($success);
        }

        $this->assertEquals(0, $breaker->storage->getFailuresCount());
    }

    public function test_if_it_will_record_every_failure()
    {
        $config = Config::make([
            'failure_threshold' => 4,
        ]);

        $breaker = new CircuitBreaker($config);

        $fail = function () {
            throw new \Exception("test");
        };

        $tries = 3;

        foreach (range(1, $tries) as $i) {
            try {
                $breaker->call($fail);
            } catch (\Exception) {

            }
        }

        $this->assertEquals($tries, $breaker->storage->getFailuresCount());
    }

    public function test_if_it_will_open_circuit_after_failure_threshold()
    {
        $config = Config::make([
            'failure_threshold' => 3,
        ]);

        $breaker = new CircuitBreaker($config);

        $fail = function () {
            throw new \Exception();
        };

        $tries = 4;

        foreach (range(1, $tries) as $i) {
            try {
                $breaker->call($fail);
            } catch (\Exception $exception) {

            }
        }

        $this->assertTrue($breaker->isOpen());
    }

    public function test_if_counter_is_reset_after_circuit_change_state_from_close_to_open()
    {
        $config = Config::make([
            'failure_threshold' => 3,
        ]);

        $breaker = new CircuitBreaker($config);

        $fail = function () {
            throw new \Exception();
        };

        $tries = 4;

        foreach (range(1, $tries) as $i) {
            try {
                $breaker->call($fail);
            } catch (\Exception $exception) {

            }
        }

        $this->assertEquals(0, $breaker->storage->getFailuresCount());
    }

    public function test_if_it_will_close_circuit_after_success_call()
    {
        $breaker = new CircuitBreaker();
        $breaker->storage->setState(CircuitState::HalfOpen);

        $success = function () {
            return true;
        };

        $breaker->call($success);

        $this->assertEquals(CircuitState::Closed, $breaker->storage->getState());
    }

    public function test_if_it_will_transit_back_to_open_state_after_first_fail()
    {
        $breaker = new CircuitBreaker();

        $breaker->storage->setState(CircuitState::HalfOpen);

        $fail = function () {
            throw new \Exception();
        };

        $this->expectException(CircuitHalfOpenFailException::class);

        $breaker->call($fail);

        $this->assertTrue($breaker->isOpen());
    }

    public function test_if_listener_is_called()
    {
        $object = new class () extends CircuitBreakerListener {
            public int $successCount = 0;
            public int $failCount = 0;

            public function onSuccess($result): void
            {
                $this->successCount++;
            }

            public function onFail($exception): void
            {
                $this->failCount++;
            }
        };

        $factory = CircuitBreaker::factory()
            ->withOptions(['failure_threshold' => 10])
            ->withListeners([$object]);

        $success = function () {
            return true;
        };

        $fail = function () {
            throw new \Exception();
        };

        $factory->call($success);
        $factory->call($success);

        try {
            $factory->call($fail);
        } catch (\Exception) {

        }

        $this->assertEquals(2, $object->successCount);
        $this->assertEquals(1, $object->failCount);
    }

    public function test_if_it_can_skip_some_exception()
    {
        $testException = new class () extends \Exception {};

        $factory = CircuitBreaker::factory()
            ->skipFailure(function (\Exception $exception) use ($testException) {
                return $exception instanceof $testException;
            });

        $factory->call(function () use ($testException) {
            throw $testException;
        });

        $this->assertEquals(0, $factory->breaker->storage->getFailuresCount());
    }

    public function test_if_it_can_fail_even_without_exception()
    {
        $factory = CircuitBreaker::factory()
            ->failWhen(function ($result) {
                if ($result instanceof \stdClass) {
                    throw new \Exception();
                }
            });

        try {
            $factory->call(fn () => new \stdClass());
        } catch (\Exception) {

        }

        $this->assertEquals(1, $factory->breaker->storage->getFailuresCount());
    }

    //    public function test_if_redis_work()
    //    {
    //        $redis = new \Redis();
    //        $redis->connect('127.0.0.1');
    //
    //        $store = new RedisStorage('test-service', $redis);
    //
    //        $config = Config::make([
    //            'recovery_time' => 60,
    //            'failure_threshold' => 3
    //        ]);
    //
    //        $breaker = new CircuitBreaker($config, $store);
    //
    //        $success = function () {
    //            return true;
    //        };
    //
    //        $result = $breaker->call($success);
    //
    //        dd($result);
    //    }
}
