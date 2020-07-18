# Concepts

Before using Simple Commerce, you'll probably want to learn about some of the concepts.

## Cart/Order

A cart is what we call an order that has not been completed. The customer can add items, remove or update items in their cart up until they checkout.

If a customer adds an item to their cart, one will be created in the database and the ID of which will be stored in the customer's session.

An order is created whenever a cart has been completed, usually once the customer checks out. When a checkout happens:

* A customer is created (if one doesn't already exist)
* The stock numbers on the purchased variants will be subtracted

Behind the scenes, Carts are stored inside the `orders` table, they are differenciated by the `is_completed` boolean. If it's not completed, it's a cart, if it is, it's an order. It's important to know that, even though orders and carts are the same model and table, they are accessed differently from each other.

## Order Statuses

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

## Products

Products are items for sale in your store. However, products are not *actually* the things a customer buys, they actually buy a variant, but we'll talk about that next.

## Product Variants

Product variants are the thing a customer purchases, not the product itself. We've found that this comes in handy in most cases like if you own a t-shirt store, you might want to sell a small, medium and large size.

Each variant has its own SKU, price and stock availability.

## Product Categories

Product Categories allow you to tag the different types of products available in your store. Your store could sell t-shirts, jeans and outdoor jackets. Each of those would be their own category and have products associated with it.

## Customers

Customers are the people who purchase items from your store.

Customers are stored as Statamic users in Simple Commerce which makes it easier for things like database relationships etc. However, if you want, you can add the `IsACustomer` trait to any Eloquent model and use that as your customer model instead.

## Tax

> Please consult an accountant or tax professional who may be able to help you decide which taxes apply to your store.

Tax will be automatically added to a customer's cart when a new line item is added. The appropriate tax rate will be used.

You can configure a different tax rate per product. For example, a store in the UK may have a standard 20% tax rate for most items and may have a 0% tax rate for children's clothes and foods.

Tax Rates can be created and configured from the `Simple Commerce > Settings > Tax Rates` page of the Control Panel.

## Shipping

Like Tax, shipping will automatically be added to a customer's cart whenever they add a new line item. 

Each country can have its own shipping zone, and inside that, different rates, depending on the price or weight of a product. The shipping rate will be chosen depending on the shipping address' country.

Shipping is something that can be disabled on specific products. For example, say you're wanting to sell a Digital Product, like an ebook, you wouldn't want to apply a shipping rate to that.

You can configure your shipping zones and rates from the `Simple Commerce > Settings > Shipping` page of the Control Panel.

## Coupons

Coupons allow you to give customers discounts from your products. There are three types of coupons that you can issue:

* a percentage discount - where a percentage of items is discounted
* a fixed discount - where a fixed amount of an items total is discounted
* free shipping

Coupons can be configured so that a customer can't redeem it until the total of their cart if over a certain amount. You can also setup a maximum amount of times the coupon can be redeemed by a customer.