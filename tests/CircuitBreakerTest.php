<?php

namespace Stfn\CircuitBreaker\Tests;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitBreakerListener;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Exceptions\CircuitOpenException;
use Stfn\CircuitBreaker\Storage\InMemoryStorage;
use Stfn\CircuitBreaker\Storage\RedisStorage;

class CircuitBreakerTest extends TestCase
{
    public function test_if_it_can_handle_function_success()
    {
        $breaker = CircuitBreaker::for('test-service');

        $result = $breaker->call(function () {
            return true;
        });

        $this->assertTrue($result);

        $object = new \stdClass();

        $result = $breaker->call(function () use ($object) {
            return $object;
        });

        $this->assertEquals($object, $result);

        $this->assertTrue($breaker->isClosed());
    }

    public function test_if_it_will_throw_an_exception_if_circuit_breaker_is_open()
    {
        $breaker = CircuitBreaker::for('test-service');
        $breaker->openCircuit();

        $this->expectException(CircuitOpenException::class);

        $breaker->call(function () {
            return true;
        });
    }

    public function test_if_it_will_record_every_failure()
    {
        $breaker = CircuitBreaker::for('test-service')
            ->withOptions([
                'failure_threshold' => 4,
            ]);

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

        $this->assertEquals($tries, $breaker->getStorage()->getCounter()->numberOfFailures());
    }

    public function test_if_it_will_open_circuit_when_failure_threshold_is_reached()
    {
        $breaker = CircuitBreaker::for('test-service')
            ->withOptions([
                'failure_threshold' => 3,
            ]);

        $fail = function () {
            throw new \Exception();
        };

        foreach (range(1, 3) as $i) {
            try {
                $breaker->call($fail);
            } catch (\Exception) {

            }
        }

        $this->assertTrue($breaker->isOpen());
        $this->assertEquals(0, $breaker->getStorage()->getCounter()->numberOfFailures());
    }

    public function test_if_it_will_close_circuit_after_consecutive_success_calls()
    {
        $breaker = CircuitBreaker::for('test-service')
            ->withOptions(['consecutive_success' => 3]);

        $breaker->getStorage()->setState(CircuitState::HalfOpen);

        $success = function () {
            return true;
        };

        $breaker->call($success);
        $breaker->call($success);

        $this->assertEquals(CircuitState::HalfOpen, $breaker->getStorage()->getState());

        $breaker->call($success);

        $this->assertEquals(CircuitState::Closed, $breaker->getStorage()->getState());
    }

    public function test_if_it_will_transit_back_to_open_state_after_first_fail()
    {
        $breaker = CircuitBreaker::for('test-service');

        $breaker->getStorage()->setState(CircuitState::HalfOpen);

        $fail = function () {
            throw new \Exception();
        };

        $this->expectException(\Exception::class);

        $breaker->call($fail);

        $this->assertTrue($breaker->isOpen());
    }

    public function test_if_it_will_transit_to_half_open_state_after_recovery_time()
    {
        $breaker = CircuitBreaker::for('test')->withOptions(['recovery_time' => 1]);
        $breaker->openCircuit();

        sleep(2);

        $breaker->call(fn () => true);

        $this->assertEquals(CircuitState::HalfOpen, $breaker->getStorage()->getState());
    }

    public function test_if_listener_is_called()
    {
        $object = new class () extends CircuitBreakerListener {
            public int $successCount = 0;
            public int $failCount = 0;

            public function onSuccess(CircuitBreaker $breaker, $result): void
            {
                $this->successCount++;
            }

            public function onFail(CircuitBreaker $breaker, $exception): void
            {
                $this->failCount++;
            }
        };

        $breaker = CircuitBreaker::for('test-service')
            ->withListeners([$object]);

        $success = function () {
            return true;
        };

        $fail = function () {
            throw new \Exception();
        };

        $breaker->call($success);
        $breaker->call($success);

        try {
            $breaker->call($fail);
        } catch (\Exception) {

        }

        $this->assertEquals(2, $object->successCount);
        $this->assertEquals(1, $object->failCount);
    }

    public function test_if_listener_before_call_is_triggered()
    {
        $object = new class () extends CircuitBreakerListener {
            public int $count = 0;

            public function beforeCall(CircuitBreaker $breaker, \Closure $action, ...$args): void
            {
                $this->count++;
            }
        };

        $breaker = CircuitBreaker::for('test-service')->withListeners([$object]);

        $breaker->call(fn () => true);
        $breaker->call(fn () => true);

        $this->assertEquals(2, $object->count);
    }

    public function test_if_it_can_skip_some_exception()
    {
        $testException = new class () extends \Exception {};

        $breaker = CircuitBreaker::for('test-service')
            ->skipFailure(function (\Exception $exception) use ($testException) {
                return $exception instanceof $testException;
            });

        $breaker->call(function () use ($testException) {
            throw $testException;
        });

        $this->assertEquals(0, $breaker->getStorage()->getCounter()->numberOfFailures());
    }

    public function test_if_it_can_fail_even_without_exception()
    {
        $breaker = CircuitBreaker::for('test-service')
            ->withOptions([
                'failure_threshold' => 3,
            ])
            ->failWhen(function ($result) {
                return $result instanceof \stdClass;
            });

        foreach (range(1, 3) as $i) {
            try {
                $breaker->call(fn () => new \stdClass());
            } catch (\Exception) {

            }
        }

        // Make sure that number of failures is reset to zero
        $this->assertEquals(0, $breaker->getStorage()->getCounter()->numberOfFailures());
        $this->assertTrue($breaker->isOpen());
    }

    public function test_if_it_can_force_open_circuit()
    {
        $breaker = CircuitBreaker::for('test-service');
        $breaker->forceOpenCircuit();

        $this->expectException(CircuitOpenException::class);

        $breaker->call(fn () => true);
    }

    public function test_if_listener_state_change_is_triggered()
    {
        $object = new class () extends CircuitBreakerListener {
            public string $state = '';

            public function onStateChange(CircuitBreaker $breaker, CircuitState $previousState, CircuitState $newState): void
            {
                $this->state .= "{$previousState->value}->{$newState->value},";
            }
        };

        $breaker = CircuitBreaker::for('test-service')->withListeners([$object]);

        $breaker->openCircuit();
        $breaker->halfOpenCircuit();
        $breaker->closeCircuit();
        $breaker->forceOpenCircuit();

        $this->assertEquals("closed->open,open->half_open,half_open->closed,closed->force_open,", $object->state);
    }

    public function test_if_it_can_set_a_new_storage()
    {
        $breaker = CircuitBreaker::for('test');

        $this->assertInstanceOf(InMemoryStorage::class, $breaker->getStorage());

        $breaker->storage(new RedisStorage(new \Redis()));

        $this->assertInstanceOf(RedisStorage::class, $breaker->getStorage());
    }
}
