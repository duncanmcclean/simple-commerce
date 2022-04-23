---
title: Installation
---

## Requirements

Simple Commerce has a couple pre-requisites. You'll need all of these installed before you can get started.

- PHP 8.0 (and above)
- Statamic 3.3
- Laravel 8 (if installing in an existing site)
- [`php-intl` PHP extension](https://www.php.net/manual/en/book.intl.php)

I'd also highly recommend enabling HTTPS on your production site for security.

## Quick Start

If you're starting from fresh, I'd recommend using the Simple Commerce Starter Kit. It comes with Simple Commerce pre-installed, along with some boilerplate views & dummy content.

You should review the starter kit's [README.md file](https://github.com/doublethreedigital/sc-starter-kit#quick-start) for install instructions.

## Standard Install

**1.** Install Simple Commerce with Composer

```bash
composer require doublethreedigital/simple-commerce
```

**2.** Run `php please sc:install` - it'll publish the Simple Commerce default blueprints, configuration file and will setup collections and taxonomies.

**3.** That's it installed. Really simple.
