# Front-end

When you install Commerce, we give you a set of default views that can be used in your project.

However, we would recommend that you customise these views or completely recreate these views for your store. We only intend these views to be used as a demo of how everything works together.

## Tags

### Commerce

#### `{{ commerce:currency_code }}`

This tag returns the code of the currency in use in the Commerce store. This will come from the [addon's configuration](./configuration.md#currency) file.

**Example output:** `gpb`

#### `{{ commerce:currency_symbol }}`

This tag returns the symbol of the currency in use in the Commerce store. This will come from the [addon's configuration](./configuration.md#currency) file.

**Example output:** `£`

#### `{{ commerce:stripe_key }}`

This tag returns your Stripe publishable key, which you can configure in your `.env` file.

**Example output:** `pk_test_h7xxxxxx......`

### Cart

#### `{{ cart }}`

This tag returns an array of the items currently in the customers' cart.

**Usage**:

```
{{ cart }}
    <h2>{{ title }}</h2>
    <span>{{ price }}</span>
{{ /cart }}
```

#### `{{ cart:count }}`

This tag returns the total number of items in the customers' cart.

**Example output:** `5`

#### `{{ cart:total }}`

This tag returns the total cost of items in the customers' cart. Note that this is without coupons or shipping costs being added.

**Example output:** `16.40`

### Products

#### `{{ products }}`

This tag returns an array of all enabled products in your Commerce store.

**Usage:**

```
{{ products }}
    <h2>{{ title }}</h2>
    <span>{{ price }}</span>
{{ /products }}
```

#### `{{ products:count }}`

This tag returns the total number of products in your Commerce store.

**Example output:** `15`
