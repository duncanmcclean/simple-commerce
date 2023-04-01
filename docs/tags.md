---
title: Tags
---

Simple Commerce provides a set of tags allowing you to build your e-commerce sites.

-   [Cart](/tags/cart)
-   [Checkout](/tags/checkout)
-   [Countries](/tags/countries)
-   [Coupon](/tags/coupon)
-   [Currencies](/tags/currencies)
-   [Customer](/tags/customer)
-   [Gateways](/tags/gateways)
-   [Regions](/tags/regions)
-   [Shipping](/tags/shipping)

### Field Whitelisting

When using Form Tags, Simple Commerce requires you to specify any additional fields you wish to be editable via front-end forms.

For example: you may wish for customers to fill in the `shipping_note` field via the `{{ sc:cart:update }}` form but you wouldn't want them filling the `order_status` field.

With 'field whitelisting', you must specify the fields you wish to allow in the Simple Commerce config file.

```php
// config/simple-commerce.php

/*
|--------------------------------------------------------------------------
| Field Whitelist
|--------------------------------------------------------------------------
|
| You may configure the fields you wish to be editable via front-end forms
| below. Wildcards are not accepted due to security concerns.
|
| https://simple-commerce.duncanmcclean.com/tags#field-whitelisting
|
*/

'field_whitelist' => [
    'orders' => [
        'shipping_name', 'shipping_address', 'shipping_address_line2', 'shipping_city', 'shipping_region',
        'shipping_postal_code', 'shipping_country', 'use_shipping_address_for_billing', 'billing_name', 'billing_address',
        'billing_address_line2', 'billing_city', 'billing_region', 'billing_postal_code', 'billing_country',
    ],

    'line_items' => [],
],
```

## Redirects

If you're using one of Simple Commerce's "form tags", you can choose to redirect the user upon submission. If no redirect is specified, the user will be redirected back to the same page.

You may use the `redirect` parameter to specify the URL you'd like to redirect the user to after the form submission has been successful.

```antlers
{{ sc:cart:addItem redirect="/cart" }}
    <input type="hidden" name="product" value="{{ id }}">
    <input type="hidden" name="quantity" value="1">
    <button class="button-primary">Add to Cart</button>
{{ /sc:cart:addItem }}
```

You may also specify an `error_redirect` parameter where the user would be taken if the form contains any validation errors.

## Form Validation

On "form tags", Simple Commerce allows you to specify a [Form Request](https://laravel.com/docs/master/validation#creating-form-requests) which will be used for validation.

Simply specify the name of the Form Request you wish to use and it'll be picked up.

```antlers
{{## Form Request: app\Http\Requests\CheckoutInformationRequest ##}}

{{ sc:cart:update request="CheckoutInformationRequest" }}

{{ /sc:cart:update }}
```

It's worth noting that although you can specify the `request` parameter on any form tag, not all of them will actually use it. Here's a list of the forms that do:

-   `{{ sc:cart:addItem }}`
-   `{{ sc:cart:updateItem }}`
-   `{{ sc:cart:update }}`
-   `{{ sc:customer:update }}`
-   `{{ sc:checkout }}`

## How the "form tags" work

Essentially, whenever you use any of Simple Commerce's "form tags", it will wrap your HTML in a `<form>` element. If you provide any optional parameters to the form tags, they will be added onto the `<form>` element (think `class`, `id`, etc).

Whenever you use the redirect/form request parameters, Simple Commerce will add some hidden inputs to the form's HTML. The values of the inputs will be encrypted to avoid them being tampered with by your users.

:::note Note!
You may disable the encryption of the hidden values if you wish by enabling the `disable_form_parameter_validation` setting in your Simple Commerce config file.
:::

## Alias

If you'd prefer not to use the shorthand of `sc` in your tags, you can also use `simple-commerce` which will work the same way.

This could be used to give more context of the tag in use to make it clear it's dealing with Simple Commerce.

```antlers
{{ simple-commerce:countries }}
```
