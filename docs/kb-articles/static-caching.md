---
title: Use Simple Commerce with Static Caching
---

Static Caching is one of the things you can use to really speed up your site - especially when using full static caching!

However, due to the nature of e-commerce and the way Simple Commerce is built, there's a few gotcha and things you'll need to workaround to get it playing nice with Simple Commerce.

## Gotchas

### CSRF

This isn't necessarily Simple Commerce related but it's worth saying that you'll need to find some way of pulling in CSRF tokens for Statamic/Simple Commerce forms, as they'll be different for every user of the site.

Otherwise, the first user to view a form on your site, will get one token, then the same token will be given to all other users who view that form. And, then if the form is submitted, the user will receive a 419 Page Expired error from Laravel.

The recommended workaround for this is to create a route in which simply returns a fresh CSRF token for the current user.

```php
// routes/web.php

Route::get('/csrf-token', function () {
    return [
        'csrf-token' => csrf_token(),
    ];
});
```

Then, in the front-end of your site, wherever you have any forms, call that route, grab the CSRF token from the response and replace the value of any `_token_` inputs with the fresh token.

### Cart/Checkout pages

If you want to display anything specific about the cart or the customer, you'll need to load it in [via AJAX](/kb-articles/ajax). Otherwise, all users will see the same information.

Alternativly, you could disable static caching for those pages.
