# All the other tags...

::: v-pre

> Side note: We use `sc` as the tag namespace in our examples, but `simple-commerce` works too.

## Currency Code
Returns the ISO code for the store's currency.

```
{{ sc:currencyCode }}
```

## Currency Symbol
Returns the symbol for the store's currency.

```
{{ sc:currencySymbol }}
```

## Categories
Returns a bunch of product categories.

```
{{ sc:categories }}
    <h2>Products in {{ title }}</h2>
    <ul>
        {{ products }}
            <li>{{ title }} - {{ from_price }}</li>
        {{ /products }}
    </ul>
{{ /sc:categories }}
```

## Products
Returns a bunch of products. This tag also supports a whole load of filtering, so that's pretty rad.

### Basic Usage

```
{{ sc:products }}
    {{ title }}

    {{ variants }}
        {{ sku }} - {{ price }}
    {{ /variants }}
{{ /sc:products }}
```

### The advanced filtering

**Get products from certain category**

```
{{ sc:products category='clothing' }}
    ...
{{ /sc:products }}
```

**Get products where something is something**
```
{{ sc:products where='is_fruit:true' }}
    ...
{{ /sc:products }}
```

**Get products, and include those that are disabled**

```
{{ sc:products include_disabled='true' }}
    ...
{{ /sc:products }}
```

**Get products, but only 5 of them**

```
{{ sc:products limit='5' }}
    ...
{{ /sc:products }}
```

**Get the count of products**

```
We currently have {{ sc:products count='true' }} products in our store!
```

**Get the first products=**

```
{{ sc:products first='true' }}
    ...
{{ /sc:products }}
```

## Product (singular)
Returns a product.

```
{{ sc:product slug='white-t-shirt' }}
    <h1>{{ title }}</h1>

    {{ variants }}
        ...
    {{ /variants }}
{{ /sc:product }}
```

## Countries
Returns some countries.

```
{{ sc:countries }}
    ...
{{ /sc:countries }}
```

## States
Returns some states.

> Fun fact: if you just want to get all states worldwide, remove the `country` parameter.

```
{{ sc:states country='US' }}
    ...
{{ /sc:states }}
```

If you want to, you can also get a count for states: `{{ sc:states country='US' count='true' }}`.

## Currencies
Returns some currencies.

```
{{ sc:currencies }}
    ...
{{ /sc:currencies }}
```

## Gateways
Returns gateway stuff: gateway name, class, payment form etc.

### Get gateway's available

```
{{ sc:gateways }}
    {{ name }}
{{ /sc:gateways }}
```

### Get the payment form

```
{{ sc:gateways }}
    {{ payment_form }}
{{ /sc:gateways }}
```

## Orders

If the customer is logged in, then information about their orders is returned from this tag. Orders are scoped to the logged in user, meaning this tag can't be used to access data about other customers.

### Get a specific order

```
{{ sc:orders get='order-uuid' }}
    ...
{{ /sc:orders }}
```

### Get a count of orders

```
You have made {{ sc:orders count='true' }} orders.
```

### Get all orders

```
{{ sc:orders }}
    ...
{{ /sc:orders }}
```

## Form

Returns a `<form>` with a set action and method to point to Simple Commerce's actions.

### Usage

**In your template:**

```html
{{ sc:form for='checkout' redirect='/thanks' class='flex flex-col w-full' }}
    <input type="text" name="name">
    <input type="text" name="email">

    <button>Checkout</button>
{{ /sc:form }}
```

**And the output in your browser:**

```html
<form action="http://yourstore.test/!/simple-commerce/checkout" method="POST" class="flex flex-col w-full">
    <input type="text" name="name">
    <input type="text" name="email">
    
    <button>Checkout</button>

    <input type="hidden" name="_token" value="someRand0mSt6ff">
    <input type="hidden" name="redirect" value="/thanks">
</form>
```

To walk you through how it works. The `for` param chooses which action and method to use. You can also add a `redirect` attribute which will redirect your user to wherever you want when the form is successful. Any other parameters you add will just be added to the `<form>` element.

We'll also add in a CSRF field for you too!