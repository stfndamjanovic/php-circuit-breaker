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

            $this->handleSucess();
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
     * @return void
     */
    public function handleFailure(\Exception $exception)
    {
        //@ToDO Add listeners here
        $this->onFailure($exception);
    }

    /**
     * @return void
     */
    public function handleSucess()
    {
        // @ToDo Add listeners here
        $this->onSucess();
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
