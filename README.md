# Circuit breaker in PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stfndamjanovic/circuit-breaker.svg?style=flat-square)](https://packagist.org/packages/stfndamjanovic/circuit-breaker)

This package provides an implementation of the circuit breaker pattern in PHP. 
You can find more info about it [here](https://learn.microsoft.com/en-us/azure/architecture/patterns/circuit-breaker).

## Installation

You can install the package via composer:

```bash
composer require stfn/php-circuit-breaker
```

## Usage

Wrap your potentially error-prone function with the circuit breaker, and it will monitor and handle failures.

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$result = CircuitBreaker::for('3rd-party-service')->call(function () {
    // Your function that could fail
});
```

## States

Circuit breaker can have 4 different states.

### Closed

In the `Closed` state, the circuit breaker is fully operational, allowing calls to the 3rd party service. Any exceptions that occur during this state are counted.

### Half Open

The Half Open state is a transitional phase where the circuit breaker allows a limited number of calls to the 3rd party service. If these calls are successful, the circuit is closed again. However, if the service continues to exhibit issues, the circuit is moved back to the `Open` state.

### Open

The `Open` state indicates that the circuit breaker has detected a critical failure, and the call method will fail immediately, throwing a `CircuitOpenException` exception.

### Force Open

`Force Open` is not part of the regular flow. It can be utilized when intentional suspension of calls to a service is required. In this state, a `CircuitForceOpenException` will be thrown.

To force the circuit breaker into the Force Open state, use the following:
```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('3rd-party-service')->forceOpenCircuit();
```
This feature provides a manual override to stop calls to a service temporarily, offering additional control when needed.
## Storage

By default, the circuit breaker uses `InMemoryStorage` as a storage driver, which is not suitable for most of PHP applications.

More useful would be to use `RedisStorage`.

```php
use Stfn\CircuitBreaker\Storage\RedisStorage;
use Stfn\CircuitBreaker\CircuitBreaker;

$redis = new \Redis();
$redis->connect("127.0.0.1");

$storage = new RedisStorage($redis);

$result = CircuitBreaker::for('3rd-party-service')
    ->storage($storage)
    ->call(function () {
        // Your function that could fail
    });
```

You could also write your implementation of storage. You should just implement `CircuitBreakerStorage` interface.

## Configuration

Each circuit breaker has default configuration settings, but you can customize them to fit your needs:
```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('3rd-party-service')
    ->withOptions([
        'failure_threshold' => 10, // Number of failures triggering the transition to the open state
        'recovery_time' => 120, // Time in seconds to keep the circuit breaker open before attempting recovery
        'sample_duration' => 60, // Duration in seconds within which failures are counted
        'consecutive_success' => 3 // Number of consecutive successful calls required to transition from half open to closed state
    ]);
```

## Interceptors

You can configure the circuit breaker to fail based on specific conditions or to skip certain types of failures:

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('3rd-party-service')
    ->failWhen(function ($result) {
        return $result->status() >= 400;
    });

$breaker = CircuitBreaker::for('3rd-party-service')
    ->skipFailure(function ($exception) {
        return $exception->getCode() < 500;
    });
```

## Listeners

You can add listeners for circuit breaker actions by extending the CircuitBreakerListener class:

```php
use Stfn\CircuitBreaker\CircuitBreakerListener;
use Stfn\CircuitBreaker\CircuitState;

class LoggerListener extends CircuitBreakerListener
{
    public function beforeCall(CircuitBreaker $breaker, \Closure $action,...$args) : void
    {
        Log::info("before call");    
    }
    
    public function onSuccess(CircuitBreaker $breaker, $result): void
    {
        Log::info($result);
    }

    public function onFail(CircuitBreaker $breaker, $exception) : void
    {
        Log::info($exception);
    }
    
    public function onStateChange(CircuitBreaker $breaker, CircuitState $previousState, CircuitState $newState)
    {
        Log::info($previousState, $newState);
    }
}
```

Attach the listener to the circuit breaker:

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('3rd-party-service')
    ->listeners([new LoggerListener()]);
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
