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
     * @return mixed
     */
    public function call(\Closure $action, ...$args)
    {
        $result = null;

        $this->beforeCall($action, $args);

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
        if (is_callable($this->breaker->skipFailureCallback)) {
            $shouldSkip = call_user_func($this->breaker->skipFailureCallback, $exception);

            if ($shouldSkip) {
                return;
            }
        }

        foreach ($this->breaker->listeners as $listener) {
            $listener->onFail($exception);
        }

        $this->onFailure($exception);

        throw $exception;
    }

    /**
     * @return void
     */
    public function handleSucess($result)
    {
        if (is_callable($this->breaker->failWhenCallback)) {
            call_user_func($this->breaker->failWhenCallback, $result);
        }

        $this->onSucess();

        foreach ($this->breaker->listeners as $listener) {
            $listener->onSuccess($result);
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
