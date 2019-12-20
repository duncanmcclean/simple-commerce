# Stripe 

[Stripe](https://stripe.com/) is one of the most popular payment gateways available which is why it's a first class citizen of Commerce for Statamic.

It [doesn't support all countries and currencies](https://stripe.com/global). In which case, it might not be best fit to use Commerce if you need your store to support a country or currency not yet supported by Stripe.

## Setting Stripe up

Before you can start accepting payments through Stripe, it would be a good idea to connect Commerce to your Stripe account. It's a really easy process.

Firstly, log into the Stripe Dashboard and go to the 'Developers' section and then the 'API Keys' page. Stripe will give you two keys: a publishable key and a secret key. Keep hold of these for a minute.

Next, you'll need to add two lines to your `.env` file and your `.env.example`.

```
STRIPE_KEY=
STRIPE_SECRET=
```

In your `.env` file, paste in the publishable key and your secret key in as values.

After that, you should be able to go through the Checkout flow and Stripe will work with real charges.

## How the Checkout flow work

If you want to understand how you get from the checkout page to having money being deducted from a customer's bank account, here's how that works (from a technical perspective).

1. We'll create a thing called a 'PaymentIntent'. A Payment Intent is a thing that tells Stripe that we intend on billing a customer a certain amount. We'll generate a 'PaymentIntent' whenever the user visits the Checkout page of your store.
2. When the user enters their card details into the field and they submit the checkout form, we'll go and ask Stripe to go along with actually billing the customer.

## Strong Customer Authentication (SCA)

Strong Customer Authentication is a new piece of legislation introduced by the European Union (and adopted by all member countries) which requires banks to ask for verification that a customer is who they say they are when making a purchase online.

There are three ways a customer could have to verify themselves, two have to be used for verification.

1. Something you know (a password or security questions)
2. Something you have (a mobile phone)
3. Something you are (fingerprint) 

Usually, Strong Customer Authentication is only used for items over a certain threshold meaning low value items may not require authentication.

Originally, SCA was meant to come into force on the 14th of September but it's been held back as many financial institutions and online merchants were not and possibly still not compatible with the new legislation.

## Test Cards

While in Stripe test mode, you can use [test cards](https://stripe.com/docs/testing#cards) to make sure the checkout flow works properly.
