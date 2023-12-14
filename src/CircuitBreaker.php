<?php

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\StateHandlers\ClosedStateHandler;
use Stfn\CircuitBreaker\StateHandlers\HalfOpenStateHandler;
use Stfn\CircuitBreaker\StateHandlers\OpenStateHandler;
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
            CircuitState::Open->value => OpenStateHandler::class
        ];

        if (!array_key_exists($state->value, $map)) {
            throw new \Exception("State {$state->value} is not valid");
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
}
