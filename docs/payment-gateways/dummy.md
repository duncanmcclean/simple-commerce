---
title: Dummy Payment Gateway
---

If you're playing around with Simple Commerce or you haven't made your mind up on which payment provider to use, then the dummy gateway can come in helpful.

## Configuration

You can configure the gateway in your `config/simple-commerce.php` config file.

```php
/*
|--------------------------------------------------------------------------
| Gateways
|--------------------------------------------------------------------------
|
| You can setup multiple payment gateways for your store with Simple Commerce.
| Here's where you can configure the gateways in use.
|
*/

'gateways' => [
    \DuncanMcClean\SimpleCommerce\Gateways\Builtin\DummyGateway::class => [],
],
```

## Usage

You can enter any Credit Card number, CVV and Expiry Date and the gateway will always return back successful.
