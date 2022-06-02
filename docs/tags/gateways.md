---
title: Gateways
---

With the gateway tag, you can loop through and use a specific gateway. This tag is commonly used on checkout pages to display payment forms.

This page has documentation on the tag itself but if you're looking for docs on displaying payment forms, head over to the [Gateway docs](/gateways#templating).

## Tags

### All gateways

This tag returns a loop of the gateways setup for your store.

```antlers
<label>Which payment gateway do you wish to use?</label>

<select>
    {{ sc:gateways }}
        <option value="{{ class }}">{{ display }}</option>
    {{ /sc:gateways }}
</select>
```

### Gateways Count

This tag returns the number of gateways you have setup for your store.

```antlers
We have {{ sc:gateways:count }} gateways available for payment!
```

### Get a gateway

This tag lets you get a particular gateway and its information, where `stripe` is the handle of the gateway.

```antlers
{{ sc:gateways:stripe }}
    <h2>Payment with {{ display }}</h2>
	<!-- Whatever else you need to do -->
{{ /sc:gateways:stripe }}
```

## Variables Available

You might have noticed in the above examples, it uses things like `{{ display }}` and `{{ class }}`. These are variables exposed by Simple Commerce. Here's a full list of the variables you can use inside the `{{ sc:gateways }}` tag.

- `name` - Name of the gateway
- `handle` - Camel cased version of the gateway name
- `class` - Class name of the gateway
- `formatted_class` - Formatted version of the gateway's class name
- `display` - Display name
- `purchaseRules` - Validation rules used on the submission of `{{ sc:checkout }}` form
- `purchaseMessages` - Validation messages used for errors after the submission of the `{{ sc:checkout`}}` form.
- `gateway-config` - Everything from the gateways's config
- `webhook_url` - Gateway's Webhook URL
