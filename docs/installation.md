---
title: Installation
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595083324
id: fe33ff01-b30c-45e7-8537-017f34a6c09d
is_documentation: true
nav_order: 2
---
## Requirements
Simple Commerce doesn't really have its own set of requirements, basically as long as [Statamic 3 works](https://statamic.dev/requirements), Simple Commerce should work too.

We do however recommend that your site has SSL setup because you're going to be processing credit cards after all. [Lets Encrypt](https://letsencrypt.org/) can give you SSL certificates for free.

## Recommended Install
We recommend installing Simple Commerce via the command line instead of through the Statamic Control Panel.

1. Install Simple Commerce with Composer

```
composer require doublethreedigital/simple-commerce
```

2. Publish Simple Commerce's vendor assets. This will give you our default blueprints, fieldtypes and configuration file.

```
php artisan vendor:publish --provider="DoubleThreeDigital\SimpleCommerce\ServiceProvider"
```

3. Get Started!

## Quick Start
If you'd prefer to get started with some boilerplate views and Simple Commerce already installed, you should checkout our the [Simple Commerce Starter Kit](https://github.com/doublethreedigital/simple-commerce-starter).