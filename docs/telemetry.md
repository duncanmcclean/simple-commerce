---
title: Telemetry
---

Every 30 days, Simple Commerce will send an **anonymized** telemetry request to my domain, containing the following information:

* Site Hash (based off your `APP_URL`, to help identify unique sites)
* Statamic version
* Simple Commerce version
* PHP version
* Timestamp of last telemetry send
* Count of orders since last telemetry send
* Sum of order grand totals since last telemetry send

This information helps me see which Simple Commerce / Statamic / PHP versions are in-use, alongside some basic statistics for marketing purposes (eg. Simple Commerce processes on average X orders per month, £x are processed by Simple Commerce on average per month).

Telemetry requests will only be sent in a production environment, on a web request.

## Opt-out of telemetry

If you wish to opt-out of Simple Commerce's Telemetry feature, you may add this to your Simple Commerce config file:

```php
// config/simple-commerce.php

'enable_telemetry' => true,
```
