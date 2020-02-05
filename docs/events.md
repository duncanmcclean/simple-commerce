Simple Commerce provides a few events which you can hook into in your `EventServiceProvider`.

### [`AddedToCart`](https://github.com/damcclean/commerce/blob/master/src/Events/AddedToCart.php)

Every time the user adds a product variant to their cart, this event is dispatched with the user's `Cart` and the `CartItem` that was added.

### [`CheckoutComplete`](https://github.com/damcclean/commerce/blob/master/src/Events/CheckoutComplete.php)

Once the user has completed the checkout flow and an order has been created within Commerce, this event is dispatched with the `order` and `customer`.

### [`NewCustomerCreated`](https://github.com/damcclean/commerce/blob/master/src/Events/NewCustomerCreated.php)

When a user completes an order, we look to see if the customer is new or already exists in the store database. If they are new, we dispatch this event with the `customer`.

### [`OrderStatusUpdated`](https://github.com/damcclean/commerce/blob/master/src/Events/OrderStatusUpdated.php)

When the status of an order is changed from the Control Panel, then this event is dispatched with the `order` and the `customer`.

### [`VariantOutOfStock`](https://github.com/damcclean/commerce/blob/master/src/Events/ProductOutOfStock.php)

When a product variant has run out of stock, this event will be dispatched with the `variant`.

### [`VariantStockRunningLow.php`](https://github.com/damcclean/commerce/blob/master/src/Events/ProductStockRunningLow.php)

When a product variant is running low on stock, this event will be dispatched with the `variant`.

### [`ReturnCustomer`](https://github.com/damcclean/commerce/blob/master/src/Events/ReturnCustomer.php)

When a user completes an order, we will look to see if the customer is new or already exists in the store database. If they already exist, we will dispatch this event with the `customer`.
