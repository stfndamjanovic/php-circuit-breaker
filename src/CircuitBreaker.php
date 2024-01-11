<?php

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\Exceptions\InvalidStateException;
use Stfn\CircuitBreaker\StateHandlers\ClosedStateHandler;
use Stfn\CircuitBreaker\StateHandlers\HalfOpenStateHandler;
use Stfn\CircuitBreaker\StateHandlers\OpenStateHandler;
use Stfn\CircuitBreaker\StateHandlers\StateHandler;
use Stfn\CircuitBreaker\Storage\CircuitBreakerStorage;
use Stfn\CircuitBreaker\Storage\InMemoryStorage;

class CircuitBreaker
{
    /**
     * @var Config
     */
    public Config $config;

    /**
     * @var CircuitBreakerStorage
     */
    public CircuitBreakerStorage $storage;

    /**
     * @var CircuitBreakerListener[]
     */
    public array $listeners = [];

    /**
     * @var \Closure|null
     */
    public \Closure|null $failWhenCallback = null;

    /**
     * @var \Closure|null
     */
    public \Closure|null $skipFailureCallback = null;

    /**
     * @param Config|null $config
     * @param CircuitBreakerStorage|null $storage
     */
    public function __construct(Config $config = null, CircuitBreakerStorage $storage = null)
    {
        $this->config = $config ?: new Config();
        $this->storage = $storage ?: new InMemoryStorage();
    }

    /**
     * @param \Closure $action
     * @param ...$args
     * @return mixed
     * @throws \Exception
     */
    public function call(\Closure $action, ...$args)
    {
        /** @var StateHandler $stateHandler */
        $stateHandler = $this->makeStateHandler();

        return $stateHandler->call($action, $args);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function makeStateHandler()
    {
        $state = $this->storage->getState();

        $map = [
            CircuitState::Closed->value => ClosedStateHandler::class,
            CircuitState::HalfOpen->value => HalfOpenStateHandler::class,
            CircuitState::Open->value => OpenStateHandler::class,
        ];

        if (! array_key_exists($state->value, $map)) {
            throw InvalidStateException::make($state->value);
        }

        return new $map[$state->value]($this);
    }

    /**
     * @return void
     */
    public function openCircuit()
    {
        $this->storage->open();
    }

    /**
     * @return void
     */
    public function closeCircuit()
    {
        $this->storage->close();
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->storage->getState() !== CircuitState::Closed;
    }

    /**
     * @param CircuitBreakerListener $listener
     * @return void
     */
    public function addListener(CircuitBreakerListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @return CircuitBreakerFactory
     */
    public static function factory()
    {
        return new CircuitBreakerFactory(new self());
    }
}
