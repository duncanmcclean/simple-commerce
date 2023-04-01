---
title: "Fix InvalidFormParametersException error"
---

You may find that this exception is thrown when submitting Simple Commerce forms but you're probably not sure **why** it's being thrown.

Simple Commerce v3.0 introduced better protection around 'hidden form parameters' (which make things like redirects and custom form requests possible).

Previously, if your user wanted to, they could edit/delete the hidden inputs in your SC forms and get around your custom redirects or form requests.

From v3.0 onwards, Simple Commerce will generate values for all of these hidden 'form params'/'hidden inputs' and it encrpts the values server-side. Then when the form is submitted, Simple Commerce reads those values. If the values don't exist in the request or have been tampered with, that's when it'll throw the `InvalidFormParametersException`.

## Common case: AJAX

A common use-case where you may run into this is when attempting to submit forms with AJAX. If you attempt to submit to a Simple Commerce endpoint, you'll need these form parameters to be generated & filled.

Currently, the recommended way to do this is to create a dummy `<form>` (using the Simple Commerce tags), then add your values as hidden inputs, then when you need to, submit the form using AJAX from the provided form.
