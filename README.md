# Simple Commerce
![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)

Simple Commerce is a perfectly simple e-commerce solution for Statamic 3. 

This repository contains the code for Simple Commerce. However, it's important to understand that you'll need a license to use this software in a production environment.

## Requirements
This addon requires the latest version of Statamic 3. You should also have MySQL (or another database system) installed and configured.

## Installation
From your terminal, run the following commands:

```
composer require doublethreedigital/simple-commerce
php artisan vendor:publish --provider=DoubleThreeDigital\SimpleCommerce\ServiceProvider
php artisan migrate
php artisan commerce:seed
```

## Resources
* [Simple Commerce Docs](./docs)
* [Simple Commerce Issues](/issues)
* [Simple Commerce Discord](https://discord.gg/P3ACYf9)
