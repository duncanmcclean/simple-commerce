---
title: Product Variants
---

Product Variants are a pretty common feature among e-commerce stores today, especially for stores that sell physical products, like t-shirts.

![Product Variants Fieldtype](/img/simple-commerce/Product-Variants-Fieldtype.png)

## How it works

For standard products, you have a price field on your Product blueprint. 

However, to use Simple Commerce's variants feature, you need to remove the Price field and add a new Product Variants field.


For standard products, you would have a Price field in your Product entries. However, to use variants, you need to remove the `Price` field and switch it for a `Product Variants` field. Make sure the handle of the field is `product_variants` or Simple Commerce won't pick it up.

:::note Note!
Make sure the handle of the field is `product_variants` or it won't get picked up.

Also, don't pick the 'Product Variant' (singular) fieldtype. It's used on orders to relate back to a particular variant. It's not what you want 😅
:::

## Options Fields

You may optionally configure fields to be visible for each variant option when editing your product blueprint.

![Option Fields](/img/simple-commerce/variant-option-fields-configure.jpg) 

The configured fields will show in the Control Panel and will be available when looping through variants in Antlers.

## Templating

### Looping through variants

If you need to loop through a product's variations, you can simply do:

```antlers
{{ product_variants:options }}
	{{ variant }} - {{ price }}
{{ /product_variants:options }}
```

### Adding to cart

To add a variant option to the cart you can use the same workflow you would with normal products. 

Although, you need to provide the 'key' of the variant option the user wants to add.

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
