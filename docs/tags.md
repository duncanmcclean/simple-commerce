---
title: Tags
---

## Available tags

To help you integrate Simple Commerce into your Antlers templates, Simple Commerce provides various tags:

* [Cart](/tags/cart)
* [Checkout](/tags/checkout)
* [Countries](/tags/countries)
* [Coupon](/tags/coupon)
* [Currencies](/tags/currencies)
* [Customer](/tags/customer)
* [Gateways](/tags/gateways)
* [Shipping](/tags/shipping)

## Form Tags

Some Simple Commerce tags output `<form>` elements that submit to Simple Commerce endpoints. There's a couple of optional parameters you can add to form tags.

* `redirect` - the URL where you'd like to redirect the user after a successful form submission.
* `error_redirect` - the URL where you'd like to redirect the user after any validation errors are thrown by the form.
* `request` - the name of the [Form Request](https://laravel.com/docs/master/validation#creating-form-requests) you wish to use for validation of the form.

```antlers
{{ sc:cart:addItem redirect="/cart" }}
    <input type="hidden" name="product" value="{{ id }}">
    <input type="hidden" name="quantity" value="1">
    <button class="button-primary">Add to Cart</button>
{{ /sc:cart:addItem }}
```

### Validation

Like noted above, you can use the `request` parameter when creating form tags to specify a [Form Request](https://laravel.com/docs/master/validation#creating-form-requests) to be used for validation purposes. You can either tell it the full class name (including the namespace) or without it.

```antlers
{{## Form Request: app\Http\Requests\CheckoutInformationRequest ##}}

{{ sc:cart:update request="CheckoutInformationRequest" }}

{{ /sc:cart:update }}
```

Although you can specify the `request` parameter on any form tag, not all of them will actually use it. Here's a list of the forms that do:

* `{{ sc:cart:addItem }}`
* `{{ sc:cart:updateItem }}`
* `{{ sc:cart:update }}`
* `{{ sc:customer:update }}`
* `{{ sc:checkout }}`

## Blade support

At the moment, I've not got any plans to introduce first-party support for Blade (or any similar templating languages for that metter).

## Alias

If you'd prefer not to use the shorthand of `sc` in your tags, you can also use `simple-commerce` which will work the same way.

This could be used to give more context of the tag in use to make it clear it's dealing with Simple Commerce.

```antlers
{{ simple-commerce:countries }}
```
