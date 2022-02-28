---
title: Installation
---

## Requirements

Simple Commerce has a couple pre-requisites. You'll need all of these installed before you can get started.

- PHP 7.4 and above (PHP 8.1 recommended)
- Statamic 3.2 and above (Statamic 3.3 recommended)
- The `php-intl` extension for PHP - it's used for currency formatting

While not strictly required, it's highly recommend you use SSL on your production site for security.

## Standard Install

While you can install Simple Commerce via the Control Panel, I'd recomend installing via Composer instead.

**1.** Install Simple Commerce with Composer

```bash
composer require doublethreedigital/simple-commerce
```

**2.** Run `php please sc:install` - it'll publish the Simple Commerce default blueprints, configuration file and will setup collections and taxonomies.

**3.** That's it installed. Really simple.

## Quick Start

If you'd prefer to get started with some boilerplate views and Simple Commerce already installed, you should checkout our the [Simple Commerce Starter Kit](https://github.com/doublethreedigital/sc-starter-kit).
