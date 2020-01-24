Simple Commerce gives you two places to configure stuff: inside your Control Panel or in the `config/commerce.php` file. It doesn't really matter *how* you update it as they offer the same settings in the end.

# Address

We require to know where your business is located so that we can generate tax and shipping prices for you and so we can display it on customer's receipts.

# Prices

We need to know what currency you want to display on your store. By default we have it set to `USD` but in reality you can change it to whatever you want (as long as its supported by Stripe).

There's also the option to change the position of the currency symbol when we display prices and what you want to use as your currency separator. 

# Stripe

Your Stripe details shouldn't be entered directly into the config file. Instead they should be put in your `.env` file, like this:

```
STRIPE_KEY=
STRIPE_SECRET=
```

# Routes

Sometimes you might want to use your own URLs because you don't want to loose SEO from your old site or maybe you just have a preference. If so, just update those things here.

For things like product and category show pages, you'll want to use `{product}` and `{category}` which will be rendered out as the slug of the product/category.

# Cart

Simple Commerce stores the cart of every customer in the database. For large stores, sometimes you might want to get rid of these untouched carts if they have been abandoned for a certain amount of time. We default this to `30` days but you can change that to whatever you want.
