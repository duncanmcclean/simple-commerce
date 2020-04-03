# Simple Commerce
![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)

Simple Commerce is a perfectly simple e-commerce solution for Statamic 3. 

This repository contains the code for Simple Commerce. However, it's important to understand that you'll need a license to use this software in a production environment.

## Requirements
This addon requires the latest version of Statamic 3. You should also have MySQL (or another database system) installed and configured.

## Installation
From your terminal, run the following commands:

```shell script
composer require doublethreedigital/simple-commerce
php artisan vendor:publish --provider=DoubleThreeDigital\SimpleCommerce\ServiceProvider
php artisan migrate
php artisan simple-commerce:seed
```

## Licensing
Like Statamic, Simple Commerce is commercial software but has an open-source codebase. If you want to use Simple Commerce in production, you'll need to buy a license. 

When Statamic 3 is launched, Simple Commerce will launch on the Marketplace, until then you can buy a license by emailing [duncan@doublethree.digital](mailto:duncan@doublethree.digital).

## Resources
* [Simple Commerce Docs](./docs)
* [Simple Commerce Issues](https://github.com/doublethreedigital/simple-commerce/issues)
* [Simple Commerce Discord](https://discord.gg/P3ACYf9)
