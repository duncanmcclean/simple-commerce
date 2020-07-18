---
title: Installation
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595083324
id: fe33ff01-b30c-45e7-8537-017f34a6c09d
is_documentation: true
nav_order: 2
---
## System Requirements
* PHP 7.2 or higher
* Composer
* A Statamic 3 site
* Some sort of web server, like Nginx

## Install Guide
1. Install Simple Commerce via Composer, we recommend doing this instead of via the Control Panel.

```
composer require doublethreedigital/simple-commerce
```

2. Publish everything Simple Commerce related: config file, fieldtypes, blueprints, etc

```
php artisan vendor:publish --provider="DoubleThreeDigital\SimpleCommerce\ServiceProvider"
```

3. Start developing!

## Quick Start
If you'd like to start with Simple Commerce already installed, and a basic front-end boilerplate, check out the [Simple Commerce starter kit](https://github.com/doublethreedigital/simple-commerce-starter).