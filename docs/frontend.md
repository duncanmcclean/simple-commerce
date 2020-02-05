By default, Simple Commerce provides you with a boilerplate front-end. We wouldn't recommend using this boilerplate in product but only as an example of how things work together.

The boilerplate should get published to your `resources/views/vendor` directory during installation, but you can also find them [on Github](../resources/views/web).

# Tags

## `{{ commerce:currencyCode }}`

Returns the code of your chosen currency.

## `{{ commerce:currencySymbol }}`

Returns the symbol of your chosen currency.

## `{{ commerce:stripKey }}`

Returns your Stripe key from your `.env` file.

## `{{ commerce:route }}`

Returns Simple Commerce routes using the route names defined in [`web.php`](https://github.com/doublethreedigital/simple-commerce/blob/master/routes/web.php).

```html
<a href="{{ commerce:route key='products.index' }}">All Products</a> 
```

The `commerce:route` tag also supports passing in parameters. For example, if you need to pass in the `product` parameter, just pass it into the tag, like so:

```html
{{ commerce:route key='products.show' product='uuid' }}
```

## `{{ commerce:categories }}`

### All Categories

```html
{{ commerce:categories }}
    <h2>{{ title }}</h2>
{{ /commerce:categories }}
```

### Count

```html
{{ commerce:categories count='true' }}
```

## `{{ commerce:products }}`

### All Products

```html
{{ commerce:products }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

### Product in Category

`category` should be the `slug` of the category you want to get products of.

```html
{{ commerce:products category='clothing' }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

### Include Disabled

Include disabled products in your results.

```html
{{ commerce:products include_disabled='true' }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

### Count

```html
{{ commerce:products count='true' }}
```

## `{{ commerce:countries }}`

Returns an array of countries.

```html
<select name="country">
    {{ commerce:countries }}
        <option value="{{ iso }}">{{ name }}</option>
    {{ /commerce:countries }}
</select>
```

## `{{ commerce:states }}`

### All States

```html
<select name="state">
    {{ commerce:states }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /commerce:states }}
</select>
```

### States in country

Returns an array of states in a country.

* `country` should be the ISO code of the country you want to get states for.

```html
<select name="state">
    {{ commerce:states country='USD' }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /commerce:states }}
</select>
```

### Count

```html
{{ commerce:states count='true' }}
```

## `{{ commerce:currencies }}`

Returns an array of currencies.

```html
<p>We support these currencies:</p>

<ul>
    {{ commerce:currencies }}
        <li>{{ name }}</li>
    {{ /commerce:currencies }}
</ul>
```

## `{{ cart:items }}`

Get all items in the customers' cart. `cart` by itself is an alias of this.

```html
{{ cart:items }}
    <h2>{{ product:title }}</h2>
{{ /cart:items }}
```

## `{{ cart:count }}`

Get a count of the items in the customers' cart.

```html
<p>There are {{ cart:count }} items in your cart.</p>
```

## `{{ cart:total }}`

Returns the total amount of the customers' cart.

```html
<p>The total of your cart {{ commerce:currency_symbol }}{{ cart:count }}.</p>
```

# Modifiers

## Price

If you want to change a price from being a number like `15` to being formatted like a currency: `$15.00`, then you should use the price modifier.

```html
{{ from_price | price }}
```

# Form Endpoints

On the front-end, Simple Commerce uses lots of form request to do things like adding to the user's cart, redeeming a coupon and processing an order. Here's a list of the form endpoints that we provide, we'll add more detailed documentation on them later.

* `/cart` - Adds an item to the user's cart
* `/cart/clear` - Clears the user's cart
* `/cart/delete` - Removes an item from the user's cart
* `/checkout` - Processes the user's information, charges the customer and creates an order
