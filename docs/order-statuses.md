---
title: Order Statuses
---

Simple Commerce has the concept of Order & Payment statuses. They help you tell what the 'state' of an order is.

When an order is initially created, it will be a **Cart** and **Unpaid**.

After a customer has submitted the checkout form or redirected back from a third-party gateway, their order will be marked as **Placed**.

Their order will then only be marked as **Paid** when we receive confirmation from the payment gateway that a payment has taken place & been successful.

In the Control Panel, admins can then mark orders as **Dispatched** or as **Cancelled**. They may also **Refund** orders.

## Available Statuses

**Order Statuses:**

-   Cart
-   Placed
-   Dispatched (renamed from Shipped)
-   Delivered
-   Cancelled

**Payment Statuses:**

-   Unpaid
-   Paid
-   Refunded

## Updating Order Status

### In the Control Panel

In the Control Panel, when you're viewing orders in the List view, you'll see a "Update Order Status" action.

### Programatically

If you wish to update the order status programatically, simply use the `updateOrderStatus` method available on `Order`s.

You should pass in an option from the `OrderStatus` enum as the first & only parameter.

```php
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;

Order::find('order-id')->updateOrderStatus(OrderStatus::Placed);
```


## Status Log

Simple Commerce also keeps track of any status changes on orders. If you view the order entry's markdown file or the order in the database, you'll see a `status_log` array with timestamps next to each of the order's timestamps.

```yaml
status_log:
    paid: '2023-02-06 20:11'
    placed: '2023-02-06 20:11'
```
