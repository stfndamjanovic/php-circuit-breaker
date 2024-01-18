# Circuit breaker in PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stfndamjanovic/circuit-breaker.svg?style=flat-square)](https://packagist.org/packages/stfndamjanovic/circuit-breaker)

This package provides an implementation of the circuit breaker pattern in PHP. 
You can find more info about it [here](https://learn.microsoft.com/en-us/azure/architecture/patterns/circuit-breaker).

## Installation

You can install the package via composer:

```bash
composer require stfndamjanovic/php-circuit-breaker
```

## Usage

Wrap your potentially error-prone function with the circuit breaker, and it will monitor and handle failures. The circuit breaker tracks the occurrence of exceptions and takes preventive measures, such as opening the circuit, if failures become too frequent. During the circuit open state, calls to the function are temporarily halted, allowing the system to recover.

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$result = CircuitBreaker::for('3rd-party-service')->call(function () {
    // Your function that could fail
});
```

### Storage

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

### Configuration

Each circuit breaker has default configuration settings, but you can customize them to fit your needs:
```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('3rd-party-service')
    ->withOptions([
        'failure_threshold' => 10,
        'recovery_time' => 120
    ]);
```

### Middlewares

You can configure the circuit breaker to fail based on specific conditions or to skip certain types of failures:

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$breaker = CircuitBreaker::for('test-service')
    ->failWhen(function ($result) {
        return $result->status() >= 400;
    });

$breaker = CircuitBreaker::for('test-service')
    ->skipFailure(function ($exception) {
        return $exception instanceof HttpException;
    });
```

### Listeners

You can add listeners for circuit breaker actions by extending the CircuitBreakerListener class:

```php
use Stfn\CircuitBreaker\CircuitBreakerListener;

class Logger extends CircuitBreakerListener
{
    public function onSuccess($result): void
    {
        Log::info($result);
    }

    public function onFail($exception) : void
    {
        Log::info($exception);
    }
}
```

Attach the listener to the circuit breaker:

```php
use Stfn\CircuitBreaker\CircuitBreaker;

$loggerListener = new Logger();

$breaker = CircuitBreaker::for('test-service')
    ->listeners([$loggerListener]);
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
