<?php

namespace Stfn\CircuitBreaker\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Config;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;
use Stfn\CircuitBreaker\Tests\TestClasses\InMemoryStore;

class CircuitBreakerTest extends TestCase
{
    public function test_if_it_can_handle_function_success()
    {
        $circuitBreaker = new CircuitBreaker($this->getDefaultConfig(), new InMemoryStore());

        $result = $circuitBreaker->run(function () {
            return true;
        });

        $this->assertTrue($result);

        $object = new \stdClass();

        $result = $circuitBreaker->run(function () use ($object) {
            return $object;
        });

        $this->assertEquals($object, $result);
    }

    public function test_if_it_will_throw_an_exception_if_circuit_breaker_is_open()
    {
        $store = new InMemoryStore();
        $store->state = CircuitState::Open;

        $circuitBreaker = new CircuitBreaker($this->getDefaultConfig(), $store);

        $this->expectException(CircuitOpenException::class);

        $circuitBreaker->run(function () {
            return true;
        });
    }

    public function test_if_it_will_record_every_success()
    {
        $store = new InMemoryStore();

        $circuitBreaker = new CircuitBreaker($this->getDefaultConfig(), $store);

        $closure = function () use ($circuitBreaker) {
            $circuitBreaker->run(function () {
                return true;
            });
        };

        $tries = 3;

        foreach (range(1, $tries) as $i) {
            $closure();
        }

        $this->assertEquals($tries, $store->counter()->getNumberOfSuccess());
        $this->assertEquals(0, $store->counter()->getNumberOfFailures());
    }

    public function test_if_it_will_record_every_failure()
    {
        $store = new InMemoryStore();

        $config = Config::make('test', [
            'max_number_of_failures' => 4,
        ]);

        $circuitBreaker = new CircuitBreaker($config, $store);

        $closure = function () use ($circuitBreaker) {
            $circuitBreaker->run(function () {
                throw new \Exception('test');
            });
        };

        $tries = 3;

        foreach (range(1, $tries) as $i) {
            try {
                $closure();
            } catch (\Exception $exception) {

            }
        }

        $this->assertEquals($tries, $store->counter()->getNumberOfFailures());
        $this->assertEquals(0, $store->counter()->getNumberOfSuccess());
    }

    public function test_if_it_will_open_circuit_after_failure_threshold()
    {
        $store = new InMemoryStore();

        $config = Config::make('test-service', [
            'max_number_of_failures' => 3,
        ]);

        $circuitBreaker = new CircuitBreaker($config, $store);

        $closure = function () use ($circuitBreaker) {
            $circuitBreaker->run(function () {
                throw new \Exception('test');
            });
        };

        $tries = 4;

        foreach (range(1, $tries) as $i) {
            try {
                $closure();
            } catch (\Exception $exception) {

            }
        }

        $this->assertTrue($circuitBreaker->isOpen());
    }

    public function test_if_counter_is_reset_after_circuit_change_state_from_close_to_open()
    {
        $store = new InMemoryStore();

        $config = Config::make('test-service', [
            'max_number_of_failures' => 3,
        ]);

        $circuitBreaker = new CircuitBreaker($config, $store);

        $closure = function () use ($circuitBreaker) {
            $circuitBreaker->run(function () {
                throw new \Exception('test');
            });
        };

        $tries = 4;

        foreach (range(1, $tries) as $i) {
            try {
                $closure();
            } catch (\Exception $exception) {

            }
        }

        $this->assertEquals(0, $store->counter()->getNumberOfSuccess());
        $this->assertEquals(0, $store->counter()->getNumberOfFailures());
    }

    public function test_if_it_will_close_circuit_after_success_calls()
    {
        $store = new InMemoryStore();
        $store->open();

        Carbon::setTestNow(Carbon::yesterday());

        $config = Config::make('service-test', [
            'open_to_half_open_wait_time' => 0,
            'number_of_success_to_close_state' => 3,
        ]);

        $circuitBreaker = new CircuitBreaker($config, $store);

        $closure = function () use ($circuitBreaker) {
            $circuitBreaker->run(function () {
                return true;
            });
        };

        $tries = 3;

        foreach (range(1, $tries) as $i) {
            $closure();
        }

        $this->assertEquals(CircuitState::Closed, $store->state());
    }

    public function test_if_it_will_transit_back_to_closed_state_after_first_fail()
    {
        $store = new InMemoryStore();
        $store->state = CircuitState::Open;

        Carbon::setTestNow(Carbon::yesterday());

        $config = Config::make('service-test', [
            'number_of_success_to_close_state' => 3,
            'open_to_half_open_wait_time' => 0,
        ]);

        $circuitBreaker = new CircuitBreaker($config, $store);

        $closure = function ($index) use ($circuitBreaker) {
            $circuitBreaker->run(function () use ($index) {
                $dataSet = [
                    true,
                    true,
                    false,
                ];

                if ($dataSet[$index] === true) {
                    return true;
                }

                throw new \Exception("Fail");
            });
        };

        $tries = 3;

        $this->expectException(\Exception::class);

        foreach (range(1, $tries) as $i) {
            $closure($i);
        }

        $this->assertTrue($circuitBreaker->isOpen());
    }

    public function getDefaultConfig()
    {
        return new Config("test-service");
    }

    //    public function test_if_it_will_fail_after_percentage_threshold_for_failure()
    //    {
    //
    //    }
    //    public function test_if_redis_work()
    //    {
    //        $redis = new \Redis();
    //        $redis->connect('127.0.0.1');
    //
    //        $store = new RedisStore("test-circuit", $redis);
    //        $store->halfOpen();
    //    }
}
