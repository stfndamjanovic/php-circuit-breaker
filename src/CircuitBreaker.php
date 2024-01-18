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
     * @var string
     */
    protected string $name;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var CircuitBreakerStorage
     */
    protected CircuitBreakerStorage $storage;

    /**
     * @var CircuitBreakerListener[]
     */
    protected array $listeners = [];

    /**
     * @var \Closure|null
     */
    protected \Closure|null $failWhenCallback = null;

    /**
     * @var \Closure|null
     */
    protected \Closure|null $skipFailureCallback = null;

    /**
     * @param string $name
     */
    private function __construct(string $name)
    {
        $this->name = $name;
        $this->config = new Config();
        $this->storage = new InMemoryStorage();
    }

    /**
     * @param \Closure $action
     * @param ...$args
     * @return mixed
     * @throws \Exception
     */
    public function call(\Closure $action, ...$args)
    {
        $this->storage->init($this->name);

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
     * @param string $service
     * @return self
     */
    public static function for(string $service)
    {
        return new self($service);
    }

    /**
     * @param array $options
     * @return $this
     */
    public function withOptions(array $options): self
    {
        $this->config = Config::make($options);

        return $this;
    }

    /**
     * @param array $listeners
     * @return $this
     */
    public function withListeners(array $listeners): self
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }

        return $this;
    }

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function skipFailure(\Closure $closure)
    {
        $this->skipFailureCallback = $closure;

        return $this;
    }

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function failWhen(\Closure $closure)
    {
        $this->failWhenCallback = $closure;

        return $this;
    }

    /**
     * @param CircuitBreakerStorage $storage
     * @return $this
     */
    public function storage(CircuitBreakerStorage $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return CircuitBreakerStorage
     */
    public function getStorage(): CircuitBreakerStorage
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * @return \Closure|null
     */
    public function getFailWhenCallback(): ?\Closure
    {
        return $this->failWhenCallback;
    }

    /**
     * @return \Closure|null
     */
    public function getSkipFailureCallback(): ?\Closure
    {
        return $this->skipFailureCallback;
    }
}
