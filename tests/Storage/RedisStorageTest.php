<?php

namespace Stfn\CircuitBreaker\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\CircuitState;
use Stfn\CircuitBreaker\Storage\RedisStorage;

class RedisStorageTest extends TestCase
{
    protected \Redis|null $redis = null;

    public function test_if_will_set_closed_state_on_init()
    {
        $storage = new RedisStorage($this->getRedisInstance());

        $storage->init(CircuitBreaker::for('test'));

        $this->assertEquals(CircuitState::Closed, $storage->getState());
    }

    public function test_if_set_state_will_change_value()
    {
        $storage = new RedisStorage($this->getRedisInstance());

        $storage->init(CircuitBreaker::for('test'));

        $this->assertEquals(CircuitState::Closed, $storage->getState());

        $storage->setState(CircuitState::HalfOpen);

        $this->assertEquals(CircuitState::HalfOpen, $storage->getState());
    }

    public function test_if_increment_failure_will_increase_number_of_failures()
    {
        $storage = new RedisStorage($this->getRedisInstance());

        $storage->init(CircuitBreaker::for('test'));

        $this->assertEquals(0, $storage->getNumberOfFailures());

        $storage->incrementFailure();

        $this->assertEquals(1, $storage->getNumberOfFailures());

        $storage->incrementFailure();


        $this->assertEquals(2, $storage->getNumberOfFailures());
    }

    public function test_if_reset_counter_will_remove_fail_count()
    {
        $storage = new RedisStorage($this->getRedisInstance());
        $storage->init(CircuitBreaker::for('test'));

        $storage->incrementFailure();
        $storage->incrementFailure();
        $storage->incrementFailure();

        $this->assertEquals(3, $storage->getNumberOfFailures());

        $storage->resetCounter();

        $this->assertEquals(0, $storage->getNumberOfFailures());
    }

    public function test_transition_to_open_state()
    {
        $storage = new RedisStorage($this->getRedisInstance());
        $storage->init(CircuitBreaker::for('test'));

        $storage->open();

        $this->assertEquals(CircuitState::Open, $storage->getState());
        $this->assertEquals(0, $storage->getNumberOfFailures());
        $this->assertNotEquals(0, $storage->openedAt());
    }

    public function test_transition_to_closed_state()
    {
        $storage = new RedisStorage($this->getRedisInstance());
        $storage->init(CircuitBreaker::for('test'));

        $storage->open();
        $storage->close();

        $this->assertEquals(CircuitState::Closed, $storage->getState());
        $this->assertEquals(0, $storage->openedAt());
    }

    public function getRedisInstance()
    {
        if (! $this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect(getenv("REDIS_HOST"));
        }

        return $this->redis;
    }

    public function tearDown(): void
    {
        $this->getRedisInstance()->flushDB();
    }
}
