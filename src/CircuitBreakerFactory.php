<?php

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\Storage\CircuitBreakerStorage;

class CircuitBreakerFactory
{
    public CircuitBreaker $breaker;

    public function __construct(CircuitBreaker $breaker)
    {
        $this->breaker = $breaker;
    }

    public function for(string $service)
    {
        $this->breaker->storage->setService($service);

        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->breaker->config = Config::make($options);

        return $this;
    }

    public function withListeners(array $listeners): self
    {
        foreach ($listeners as $listener) {
            $this->breaker->addListener($listener);
        }

        return $this;
    }

    public function skipFailure(\Closure $closure)
    {
        $this->breaker->skipFailureCallback = $closure;

        return $this;
    }

    public function failWhen(\Closure $closure)
    {
        $this->breaker->failWhenCallback = $closure;

        return $this;
    }

    public function storage(CircuitBreakerStorage $storage)
    {
        $this->breaker->storage = $storage;

        return $this;
    }

    public function call(\Closure $action, ...$args)
    {
        return $this->breaker->call($action, $args);
    }
}
