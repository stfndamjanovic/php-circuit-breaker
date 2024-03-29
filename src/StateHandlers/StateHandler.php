<?php

namespace Stfn\CircuitBreaker\StateHandlers;

use Stfn\CircuitBreaker\CircuitBreaker;

class StateHandler
{
    /**
     * @var CircuitBreaker
     */
    protected CircuitBreaker $breaker;

    /**
     * @param CircuitBreaker $breaker
     */
    public function __construct(CircuitBreaker $breaker)
    {
        $this->breaker = $breaker;
    }

    /**
     * @param \Closure $action
     * @param ...$args
     * @return mixed|null
     * @throws \Exception]
     */
    public function call(\Closure $action, ...$args)
    {
        $result = null;

        $this->beforeCall($action, $args);

        foreach ($this->breaker->getListeners() as $listener) {
            $listener->beforeCall($this->breaker, $action, $args);
        }

        try {
            $result = call_user_func($action, $args);

            $this->handleSucess($result);
        } catch (\Exception $exception) {
            $this->handleFailure($exception);
        }

        return $result;
    }

    /**
     * @param \Closure $action
     * @param ...$args
     * @return void
     */
    public function beforeCall(\Closure $action, ...$args)
    {

    }

    /**
     * @param \Exception $exception
     * @return mixed
     * @throws \Exception
     */
    public function handleFailure(\Exception $exception)
    {
        if (is_callable($this->breaker->getSkipFailureCountCallback())) {
            $shouldSkip = call_user_func($this->breaker->getSkipFailureCountCallback(), $exception);

            if ($shouldSkip) {
                throw $exception;
            }
        }

        $this->breaker->getStorage()->incrementFailure();

        foreach ($this->breaker->getListeners() as $listener) {
            $listener->onFail($this->breaker, $exception);
        }

        $this->onFailure($exception);

        throw $exception;
    }

    /**
     * @param $result
     * @return void
     */
    public function handleSucess($result)
    {
        $this->onSucess();

        foreach ($this->breaker->getListeners() as $listener) {
            $listener->onSuccess($this->breaker, $result);
        }
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    public function onFailure(\Exception $exception)
    {

    }

    /**
     * @return void
     */
    public function onSucess()
    {

    }
}
