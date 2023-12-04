# Circuit breaker in PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stfndamjanovic/circuit-breaker.svg?style=flat-square)](https://packagist.org/packages/stfndamjanovic/circuit-breaker)
[![Tests](https://img.shields.io/github/actions/workflow/status/stfndamjanovic/circuit-breaker/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stfndamjanovic/circuit-breaker/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/stfndamjanovic/circuit-breaker.svg?style=flat-square)](https://packagist.org/packages/stfndamjanovic/circuit-breaker)

This is implementation of circuit breaker in PHP.

## Installation

You can install the package via composer:

```bash
composer require stfndamjanovic/php-circuit-breaker
```

## Usage

```php
use Stfn\CircuitBreaker\CircuitBreaker;
use Stfn\CircuitBreaker\Stores\RedisStore;
use Redis;
use Stfn\CircuitBreaker\Config;

$redis = new Redis('127.0.0.1');
$redis->connect();

$store = new RedisStore($redis);

$config = new Config("unique-service-name");

$circuitBreaker = new CircuitBreaker($config, $store);

$circuitBreaker->run(function () {
    // Your function that could fail
});
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stefan Damjanovic](https://github.com/stfndamjanovic)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
