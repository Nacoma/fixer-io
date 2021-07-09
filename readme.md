# Fixer.io API Wrapper

[![tests](https://github.com/Nacoma/fixer-io/actions/workflows/tests.yml/badge.svg)](https://github.com/Nacoma/fixer-io/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/Nacoma/fixer-io/branch/main/graph/badge.svg?token=BU6X7E5K9N)](https://codecov.io/gh/Nacoma/fixer-io)

A wrapper around [Fixer.io](https://fixer.io/)'s currency conversion rates API.

## Supported Endpoints

- Supported Symbols
- Latest Rates
- Historical Rates
- Convert Currency
- Time Series
- Fluctuation

## Usage

```php

use Nacoma\Fixer\ExchangeFactory;
use Nacoma\Fixer\Http\Client;
use Nacoma\Fixer\Http\Middleware\ETagMiddleware;

$client = new Client(new Psr18Client(), [
    new ETagMiddleware(
        new SimpleCache(),
        new Psr17ResponseFactory(),
        new Psr17StreamFactory(),
    )
]);

$exchangeFactory = new ExchangeFactory(
    $client,
    new Psr17RequestFactory(),
    new Psr17UriFactory(),
    'your-access-key',
);

$exchange = $exchangeFactory->create('USD', ['EUR', 'JPY']);

// obtaining all rates
$result = $exchange->latestRates();

// manual conversion
$converted = 50 * $result->rates['EUR'];

// API endpoint conversion
$converted = $exchange->convert('USD', 'EUR', 50);
```

The optional `Nacoma\Fixer\Http\Client` is a thin Psr-18 compatible wrapper around any HTTP client. It enables custom middleware
chaining in order to support caching via the `ETag` header.
