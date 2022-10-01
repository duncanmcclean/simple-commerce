---
title: Handling Donations
---

"Can Simple Commerce handle donations?" is a question I get asked quite a lot. I thought I'd document my standard approach to implementing donations:

> Bear in mind that this approach only works if you're allowing customers to donate in whole amounts (eg. £5, £10 not £16.71).

## 1. Create a product

First things first, you'll need to create a Donations Product. You should make the Price of this product £1 (or the equivalent in your currency).

## 2. Adding a donation to the cart

Next, you'll want a way for customers to be able to add the Donation product to their cart. However, you're probably going to need a way for the customer to enter the amount they wish to donate. We'll use the quantity input for this.

Here's an example of what the 'add to cart' form might look like when handling donations:

```antlers
{{ sc:cart:addItem }}
    <input type="hidden" name="product" value="id-of-your-donation-product">
    <input type="number" name="quantity" min="1" placeholder="Amount (£)">

    <button type="submit">Add Donation</button>
{{ /sc:cart:addItem }}
```

And that's you done - it's that simple!
