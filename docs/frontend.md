# Front-end

Simple Commerce provides a few tags you can use and various other things to help you craft a unique front-end for your store. We've built an [example store theme layout](https://github.com/doublethreedigital/simple-commerce-example) if you're looking for use cases.

## Tags

### `{{ simple-commerce:currencyCode }}`

Returns the code of your chosen currency.

### `{{ simple-commerce:currencySymbol }}`

Returns the symbol of your chosen currency.

### `{{ simple-commerce:categories }}`

#### All Categories

```html
{{ simple-commerce:categories }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:categories }}
```

#### Count

```html
{{ simple-commerce:categories count='true' }}
```

### `{{ simple-commerce:products }}`

#### All Products

```html
{{ simple-commerce:products }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:products }}
```

#### Product in Category

`category` should be the `slug` of the category you want to get products of.

```html
{{ simple-commerce:products category='clothing' }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:products }}
```

#### Where

You can get products where a field is something. For example if I want to get products where the `is_enabled` is `true`, I'd do something like this:

```html
{{ simple-commerce:products where='slug:toothbrush' }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:products }}
```

It would output a loop of products that have are enabled. However, if you actually wanted to do that, using the next parameter would be nicer 😃

#### Include Disabled

Include disabled products in your results.

```html
{{ simple-commerce:products include_disabled='true' }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:products }}
```

#### Count

```html
{{ simple-commerce:products count='true' }}
```

#### First

Sometimes you may come across a situation where you just want to get the first product in the results. That is what `first` is for.

```html
{{ simple-commerce:products first='true' }}
    <h2>{{ title }}</h2>
{{ /simple-commerce:products }}
```

### `{{ simple-commerce:countries }}`

Returns an array of countries.

```html
<select name="country">
    {{ simple-commerce:countries }}
        <option value="{{ iso }}">{{ name }}</option>
    {{ /simple-commerce:countries }}
</select>
```

### `{{ simple-commerce:states }}`

#### All States

```html
<select name="state">
    {{ simple-commerce:states }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /simple-commerce:states }}
</select>
```

#### States in country

Returns an array of states in a country.

* `country` should be the ISO code of the country you want to get states for.

```html
<select name="state">
    {{ simple-commerce:states country='USD' }}
        <option value="{{ abreviation }}">{{ name }}</option>
    {{ /simple-commerce:states }}
</select>
```

### Form

Returns a `<form>` with a set action and method to point to Simple Commerce's actions.

#### Example

Here's a quick example of a checkout form that uses the `simple-commerce:form` tag.

**In your template:**

```html
{{ simple-commerce:form for='checkout' redirect='/thanks' class='flex flex-col w-full' }}
    <input type="text" name="name">
    <input type="text" name="email">

    <button>Checkout</button>
{{ /simple-commerce:form }}
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
{{ simple-commerce:states count='true' }}
```

### `{{ simple-commerce:currencies }}`

Returns an array of currencies.

```html
<p>We support these currencies:</p>

<ul>
    {{ simple-commerce:currencies }}
        <li>{{ name }}</li>
    {{ /simple-commerce:currencies }}
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
<p>The total of your cart {{ simple-commerce:currency_symbol }}{{ cart:count }}.</p>
```

## Modifiers

### Price

If you want to change a price from being a number like `15` to being formatted like a currency: `$15.00`, then you should use the price modifier.

```html
{{ from_price | price }}
```
