<?php

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\Storage\CircuitBreakerStorage;

class CircuitBreakerFactory
{
    public CircuitBreaker $circuitBreaker;

    public static function make()
    {
        $object = new self();
        $object->circuitBreaker = new CircuitBreaker();

        return $object;
    }

    public function for(string $service)
    {
        $this->circuitBreaker->storage->setService($service);

        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->circuitBreaker->config = Config::make($options);

        return $this;
    }

    public function withListeners(array $listeners): self
    {
        foreach ($listeners as $listener) {
            $this->circuitBreaker->addListener($listener);
        }

        return $this;
    }

    public function skipFailure(\Closure $closure)
    {
        $this->circuitBreaker->skipFailureCallback = $closure;

        return $this;
    }

    public function failWhen(\Closure $closure)
    {
        $this->circuitBreaker->failWhenCallback = $closure;

        return $this;
    }

    public function storage(CircuitBreakerStorage $storage)
    {
        $this->circuitBreaker->storage = $storage;

        return $this;
    }

    public function call(\Closure $action, ...$args)
    {
        return $this->circuitBreaker->call($action, $args);
    }
}
