---
title: 'Cart'
---

## Cart Information

`{{ sc:cart }}` returns an augmented version of the Cart entry.

```antlers
{{ sc:cart }}
  	<h2>Order {{ title }}</h2>
  	<p>Your order has been successful and will be fulfilled shortly.</p>
{{ /sc:cart }}
```

## Line Items

This is probably the most common use case for the `sc:cart` tag, fetching items from the cart.

The variables available in this tag are also augmented. Allowing you to get data on the attached product, like this: `{{ product:short_description }}`.

```antlers
{{ sc:cart:items }}
	{{ product:title }} - {{ quantity }} - {{ total }}
{{ /sc:cart:items }}
```

To get a count of the items in the customers' cart, use `{{ sc:cart:count }}`.

To get the total quantity of products in the customers' cart, use `{{ sc:cart:quantityTotal }}`.

When you're looping through line items, you may do some bits like this:

-   You can get the tax amount for the current line item with `{{ tax:amount }}`
-   You can get the total including tax with `{{ total_including_tax }}`

## Check if customer has a cart

This tag allows you to check if the current customer has a cart attached to them. It'll return a boolean, meaning you can use it in one of Antlers' if statements.

```antlers
{{ if {sc:cart:has} === false }}
  	<p>There's nothing in your cart. <a href="#">Start shopping</a>.</p>
{{ /if }}
```

## Totals

There's tags for each of the different totals in a cart.

-   `{{ sc:cart:total }}` - Returns the overall/grand total of the cart
-   `{{ sc:cart:grand_total }}` - Does the same thing as `sc:cart:total`
-   `{{ sc:cart:items_total }}` - Returns the total of all line items.
-   `{{ sc:cart:shipping_total }}` - Returns the shipping total of the cart.
-   `{{ sc:cart:shipping_total_with_tax }}` - Return the shipping total, inclusive of any tax.
-   `{{ sc:cart:tax_total }}` - Returns the tax total of the cart.
-   `{{ sc:cart:tax_total_split }}` - Returns the tax total of the cart, split by tax rate.
-   `{{ sc:cart:coupon_total }}` - Returns the total amount saved from coupons.
-   `{{ sc:cart:items_total_with_tax }}` - Returns the total of all line items, inclusive of any tax.

If you need the 'raw' value for any of these totals, meaning the integer, rather than the formatted currency amount, you can do this: `{{ sc:cart:raw_grand_total }}`.

If you find yourself needing to check if an order is 'free' (grand total is £0), then you can use the `{{ sc:cart:free }}` tag:

```antlers
{{ if {sc:cart:free} === true }}
    You have nothing to pay!
{{ else }}
    You have stuff to pay - cough up!
{{ /if }}
```

## Add Item to Cart

This tag allows you to add a product or variant to the cart. It's a [form tag](/tags#form-tags) so you need to provide a couple of parameters (form fields) when submitting:

-   `product` - The ID of the product you want to add to the cart.
-   `variant` - If applicable, the key of the variant you wish to add to the cart. Bear in mind, you will also need to provide the `product` with this.
-   `quantity` - The quantity of the line item you're adding.

```antlers
{{ sc:cart:addItem }}
  <input type="hidden" name="product" value="{{ id }}">
  <input type="number" name="quantity" value="2">
{{ /sc:cart:addItem }}
```

If you want to store any additional information with a line item (like as customisation text), simply add an additional input inside the `{{ sc:cart:addItem }}` form:

```antlers
{{ sc:cart:addItem }}
  <input type="hidden" name="product" value="{{ id }}">
  <input type="number" name="quantity" value="1">
  <input type="text" name="custom_name"> {{# [tl! **] #}}
{{ /sc:cart:addItem }}
```

Before the additional information is saved, you will need to whitelist the field in your Simple Commerce config:

```php
'field_whitelist' => [
    'orders' => [
        'shipping_name', 'shipping_address', 'shipping_address_line1', 'shipping_address_line2', 'shipping_city',
        'shipping_region', 'shipping_postal_code', 'shipping_country', 'shipping_note', 'shipping_method',
        'use_shipping_address_for_billing', 'billing_name', 'billing_address', 'billing_address_line2',
        'billing_city', 'billing_region', 'billing_postal_code', 'billing_country',
    ],

    'line_items' => ['custom_name'], // [tl! **]

    'customers' => ['name', 'email'],
],
```

Now, when you submit the "add to cart" form, the additional data will be saved as "metadata" on the Line Item:

![Viewing Line Item Metadata in the Control Panel](/img/simple-commerce/line-item-metadata.png)

## Update Line Item

With this tag, you can update a specific item in your cart. It's a [form tag](/tags#form-tags).

The tag itself requires an `item` parameter which should be the ID of the specfic line item you wish to update. You may then provide the parameters you wish to update on the item as input fields, like the below example:

```antlers
{{ sc:cart:updateItem :item="id" }}
  <input type="number" name="quantity" value="2">
{{ /sc:cart:updateItem }}
```

Alternatively, if you don't have easy access to the ID of the line item, you can pass in the product ID instead:

```antlers
{{ sc:cart:updateItem :product="id" }}
  <input type="number" name="quantity" value="2">
{{ /sc:cart:updateItem }}
```

## Remove Line Item

This tag allows you to remove an item from the cart. It's a [form tag](/tags#form-tags) and the only required parameter is on the tag itself: the `item` parameter should be the ID or the specific line item you wish to remove from the cart.

```antlers
{{ sc:cart:removeItem :item="id" }}
  <button type="submit">Remove item from cart</button>
{{ /sc:cart:removeItem }}
```

Alternatively, if you don't have easy access to the ID of the line item, you can pass in the product ID instead:

```antlers
{{ sc:cart:removeItem :product="id" }}
  <button type="submit">Remove item from cart</button>
{{ /sc:cart:removeItem }}
```

## Update Cart

This tag can be used to update any field values in the cart, kinda like [Workshop](https://statamic.com/addons/statamic/workshop), but just for carts. You can send whatever parameters you want, just ensure they are added to the entry blueprint for your orders.

```antlers
{{ sc:cart:update }}
  <input type="text" name="delivery_note">
{{ /sc:cart:update }}
```

:::tip Hot Tip
If you want to also update the customer at the same time, something like the below should work. Remember the `email`, it's required.
:::

```antlers
<input type="text" name="customer[name]">
<input type="email" name="customer[email]">
```

## Empty Cart

If you want to empty all the items from the cart and start from scratch. You can use the `{{ sc:cart:empty }}` tag. It doesn't accept any parameters.

```antlers
{{ sc:cart:empty }}
  <button>I messed up.. there's too much in my cart. I need a fresh start.</button>
{{ /sc:cart:empty }}
```

## Checking if a product exists in the customer's cart

Sometimes you'll want to know if a certain product (or product variant) exists in a customer's cart. Well, it's a good thing it's easy peasy to check.

**Standard Products**

```antlers
{{ if {sc:cart:alreadyExists :product="id"} }}
  This product is already in your cart.
{{ /if }}
```

**Variant Products**

```antlers
{{ if {sc:cart:alreadyExists :product="id" variant="Red_Small"} }}
  This product is already in your cart.
{{ /if }}
```
