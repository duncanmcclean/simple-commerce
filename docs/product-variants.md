---
title: Product Variants
---

Product Variants are a pretty common feature amoung e-commerce stores today, especially for stores that sell physical products, like t-shirts.

![Product Variants Fieldtype](/assets/Product-Variants-Fieldtype.png)

## How it works

Normally, you'd just have a `Price` field on your product but if you want to use variants, you can switch it out for a `Product Variants` field.

Simple Commerce will detect if it's a variants enabled product or not and it will adapt.

> **Note:** Make sure the handle of your product variants field is `product_variants`. Simple Commerce isn't clever enough to find it with any handle.

## Templating
You can loop over variants just like you would with any grid of data in Antlers.

```antlers
{{ product_variants:options }}
	{{ variant }} - {{ price }}
{{ /product_variants:options }}
```

### Adding to cart
To add a variant option to the cart you can use the same workflow you would with normal products. Although, now - you need to also provide the 'key' of the variant option the user wants to add.

```antlers
{{ sc:cart:addItem }}
	<input type="hidden" name="product" value="{{ id }}">

    <select name="variant">
		<option disabled>Variant</option>
		{{ product_variants:options }}
			<option value="{{ key }}">{{ variant }} {{ price }}</option>
        {{ /product_variants:options }}
    </select>

    <select name="quantity">
        <option disabled>Quantity</option>

        {{ loop from="1" to="5" }}
        	<option value="{{ value }}">{{ value }}x</option>
        {{ /loop }}
    </select>

    <button type="submit">Add to cart</button>
{{ /sc:cart:addItem }}
```
