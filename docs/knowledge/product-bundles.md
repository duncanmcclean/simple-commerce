# Product Bundles

Simple Commerce doesn't include an out of the box solution for dealing with product bundles. However, they are relativly easy to implement yourself with just a little bit of custom code.

The best solution would be to create a controller that gets hit by your frontend. That contoller would talk to the `Cart` facade, provided by Simple Commerce, and would add the relavent line items manually with the correct prices.

## Some example code

For each of the variants you want to add, you'll need to manually create the line item, which as explained above can be done using the `Cart` facade.

```php
Cart::addLineItem(
    Session::get(config('simple-commerce.cart_session_key')),
    'some-product-uu1d',
    1,
    'This item was added by a bundle.'
);
```

The first parameter, you'll need to pass in the customer's session key for the cart, second the UUID for the variant you wish to add, third the quantity and fourth you can optionally pass in a note.

If you'd like to have a fixed total for each of items combined, you'd have to split the price of the set total between the items manually, like shown below. This ensures that whenever the cart totals are recalculated, your price won't be overridden.

```php
$order->update(['price' => 10.00]);
```