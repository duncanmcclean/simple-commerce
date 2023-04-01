---
title: 'Information not being saved onto orders/customers'
---

A common issue folks run into is that information submitted in your cart-related forms aren't being saved onto the order/customer entries as you'd expect.

The reason for this is due to [Field Whitelisting](/tags#content-field-whitelisting) feature in Simple Commerce. Essentially, field whitelisting is a 'whitelist' of fields that are allowed to be updated via Simple Commerce's forms.

Field Whitelisting was added to prevent your users from adding their own `<input>` fields to Simple Commerce forms which would let them save whatever they want onto your orders/customers.

There's a few different 'whitelists':

-   Orders
-   Line Items
-   Customer

The **Orders** whitelist is for any kind of data you want saved directly on the Order entry.

The **Line Items** whitelist is for any bits of metadata you wish to save onto Line Items. You can update Line Item Metadata in the `{{ sc:cart:addItem }}` and `{{ sc:cart:updateItem }}` forms.

And, finally, the **Customer** whitelist is for any kind of data you wish to be saved onto the Customer entry (if one exists). You can save data onto the customer entry by using the customer array syntax on your forms:

```antlers
{{ sc:cart:update }}
    <input type="date" name="customer[dob]">
{{ /sc:cart:update }}
```

> The above example saves to the `dob` field on the related Customer entry.

You can configure the whitelisted fields for each of the 'whitelists' in your Simple Commerce config file:

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
        'shipping_name', 'shipping_address', 'shipping_address_line1', 'shipping_address_line2', 'shipping_city',
        'shipping_region', 'shipping_postal_code', 'shipping_country', 'shipping_note', 'shipping_method',
        'use_shipping_address_for_billing', 'billing_name', 'billing_address', 'billing_address_line2',
        'billing_city', 'billing_region', 'billing_postal_code', 'billing_country',
    ],

    'line_items' => [],

    'customers' => ['name', 'email'],
],
```
