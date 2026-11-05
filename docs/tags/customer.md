---
title: Customer
---

## Get a customer

This tag allows you to get a customer by their ID. Remember to provide the `id` parameter which should be the ID of the customer.

Customer information and order history are only returned when the logged in user has the customer's email address. Otherwise, these tags return no data. This applies to `sc:customer`, `sc:customer:orders`, and `sc:customer:order`.

```antlers
{{ sc:customer id="ff7ddbf2-cd01-4689-a57f-26cb2e7c96f9" }}
  Your name is {{ name }} and your email is {{ email }}.
{{ /sc:customer }}
```

### Update a customer

This tag allows you to update an existing customer. Remember to provide the `id` parameter which should be the ID of the customer.

```antlers
{{ sc:customer:update id="ff7ddbf2-cd01-4689-a57f-26cb2e7c96f9" }}
  <input type="text" name="name">
{{ /sc:customer:update }}
```

:::note Note!
A customer can only be updated by the logged in user with the same email address. Being attached to the current cart does not grant access to the customer's profile. Requests to the customer JSON endpoint also require a logged in user with the same email address.
:::

### Get orders by customer

This tag allows you to loop through orders created by a customer. Remember to provide the `customer` parameter which should be the ID of the customer.

```antlers
{{ sc:customer:orders customer="ff7ddbf2-cd01-4689-a57f-26cb2e7c96f9" }}
  {{ title }} - {{ grand_total }}
{{ /sc:customer:orders }}
```

### Get order by customer

This tag allows you to get an order created by a customer. This tag has two parameters. `id` for the ID of the order you want to get and `customer` is the ID of the customer you want to get.

```antlers
{{ sc:customer:order customer="ff7ddbf2-cd01-4689-a57f-26cb2e7c96f9" id="84b28c73-3a04-478f-9447-68df026c44fe" }}
  {{ title }} - {{ grand_total }}
{{ /sc:customer:order }}
```
