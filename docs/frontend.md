# Front-end

Simple Commerce provides a few tags you can use and various other things to help you craft a unique front-end for your store. We've built an [example store theme layout](https://github.com/doublethreedigital/simple-commerce-example) if you're looking for use cases.

## Tags

### `{{ commerce:currencyCode }}`

Returns the code of your chosen currency.

### `{{ commerce:currencySymbol }}`

Returns the symbol of your chosen currency.

### `{{ commerce:categories }}`

#### All Categories

```html
{{ commerce:categories }}
    <h2>{{ title }}</h2>
{{ /commerce:categories }}
```

#### Count

```html
{{ commerce:categories count='true' }}
```

### `{{ commerce:products }}`

#### All Products

```html
{{ commerce:products }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

#### Product in Category

`category` should be the `slug` of the category you want to get products of.

```html
{{ commerce:products category='clothing' }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

#### Include Disabled

Include disabled products in your results.

```html
{{ commerce:products include_disabled='true' }}
    <h2>{{ title }}</h2>
{{ /commerce:products }}
```

#### Count

```html
{{ commerce:products count='true' }}
```

### `{{ commerce:countries }}`

Returns an array of countries.

```html
<select name="country">
    {{ commerce:countries }}
        <option value="{{ iso }}">{{ name }}</option>
    {{ /commerce:countries }}
</select>
```

### `{{ commerce:states }}`

#### All States

```html
<select name="state">
    {{ commerce:states }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /commerce:states }}
</select>
```

#### States in country

Returns an array of states in a country.

* `country` should be the ISO code of the country you want to get states for.

```html
<select name="state">
    {{ commerce:states country='USD' }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /commerce:states }}
</select>
```

### Form

Returns a `<form>` with a set action and method to point to Simple Commerce's actions.

#### Example

Here's a quick example of a checkout form that uses the `commerce:form` tag.

**In your template:**

```html
{{ commerce:form for='checkout' redirect='/thanks' class='flex flex-col w-full' }}
    <input type="text" name="name">
    <input type="text" name="email">

    <button>Checkout</button>
{{ /commerce:form }}
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

We'll also add in a CSRF field for you too, for good luck!

#### Count

```html
{{ commerce:states count='true' }}
```

### `{{ commerce:currencies }}`

Returns an array of currencies.

```html
<p>We support these currencies:</p>

<ul>
    {{ commerce:currencies }}
        <li>{{ name }}</li>
    {{ /commerce:currencies }}
</ul>
```

### `{{ cart:items }}`

Get all items in the customers' cart. `cart` by itself is an alias of this.

```html
{{ cart:items }}
    <h2>{{ product:title }}</h2>
{{ /cart:items }}
```

### `{{ cart:count }}`

Get a count of the items in the customers' cart.

```html
<p>There are {{ cart:count }} items in your cart.</p>
```

### `{{ cart:total }}`

Returns the total amount of the customers' cart.

```html
<p>The total of your cart {{ commerce:currency_symbol }}{{ cart:count }}.</p>
```

## Modifiers

### Price

If you want to change a price from being a number like `15` to being formatted like a currency: `$15.00`, then you should use the price modifier.

```html
{{ from_price | price }}
```
