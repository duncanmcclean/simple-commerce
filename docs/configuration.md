Simple Commerce gives you a lot of settings, some can be updated in the Control Panel, others in the config file.

## Control Panel

In the Control Panel, you can update order statuses, shipping zones and tax rates. To manage settings from the Control Panel, go to the `Settings` navigation item under `Simple Commerce` and you can go through the settings from there.

## Config file

Simple Commerce pushes its own configuration file which is required for Simple Commerce to work properly.

### Address

We require to know where your business is located so that we can generate tax and shipping prices for you and so we can display it on customer's receipts.

### Prices

We need to know what currency you want to display on your store. By default we have it set to `USD` but in reality you can change it to whatever you want (as long as its supported by Stripe).

There's also the option to change the position of the currency symbol when we display prices and what you want to use as your currency separator. 

### Stripe

Your Stripe details shouldn't be entered directly into the config file. Instead they should be put in your `.env` file, like this:

```
STRIPE_KEY=
STRIPE_SECRET=
```

### Routes

Sometimes you might want to use your own URLs because you don't want to loose SEO from your old site or maybe you just have a preference. If so, just update those things here.

For things like product and category show pages, you'll want to use `{product}` and `{category}` which will be rendered out as the slug of the product/category.

### Tax

You can choose if the prices of your variants include tax or not, if they don't already include tax, we'll add an appropriate tax rate.

We also need to know where we should be calculating tax from, is it from your company's address, the billing address or the customer's shipping address?

And the last thing is only really needed if the first option is `false` but it lets you configure if you want the variant prices that show in your product index and product show pages to include tax or not.

### Cart

Simple Commerce stores the cart of every customer in the database. For large stores, sometimes you might want to get rid of these untouched carts if they have been abandoned for a certain amount of time. We default this to `30` days but you can change that to whatever you want.

### Notifications

This is where you can control where you want your back of store notifications sent to. Currently the only options are `mail` and `slack`.

Depending on your option, you'll need to fill in other values, like your slack webhook or your to email.
