> Note to self: add section about new tax, shipping, discounts etc

# Cart

A cart is what the customer adds products to and checks out with. A cart contains a list of product variants, discounts applied, taxes and shipping rates and any other relevant information.

Once a customer wants to actually purchase the cart, an order is created and the cart is then removed from the database.

# Orders

Orders are created when a customer checks out the items from their cart. Orders can only be created from the front-end of your store.

# Order Statuses

Order Statuses are self explanatory. The status of an order tells you what stage of fulfilment the order is at. Each store will have a default order status which is used for new orders.

Simple Commerce ships with two order statuses - `New` and `Shipped`. Each order status can have its own colour. The colours you can choose from are pretty limited though:

* `gray`
* `green`
* `blue`
* `red`
* `yellow`
* `orange`
* `pink`
* `purple`

You can change the status of orders either in the order pages or you can select an order status in the dropdown in order listing pages.

# Products

Products are items for sale in your store. However, products are not *actually* the things a customer buys, they actually buy a variant, but we'll talk about that next.

# Product Variants

Product variants are the thing a customer purchases, not the product itself. We've found that this comes in handy in most cases like if you own a t-shirt store, you might want to sell a small, medium and large size.

Each variant has its own SKU, price and stock availability.

# Product Categories

Product Categories allow you to tag the different types of products available in your store. Your store could sell t-shirts, jeans and outdoor jackets. Each of those would be their own category and have products associated with it.

# Customers

Customers are the people who buy things from your store. 

A customer will always be created if a customer does not already exist with the email used during checkout. If that email is the same as an existing customer, an existing customer will be used instead.

Customers will have addresses and orders attached to them.

# Tax

> Please consult an accountant or tax professional who may be able to help you decide which taxes apply to your store.

Work in progress.

# Shipping

Work in progress.
