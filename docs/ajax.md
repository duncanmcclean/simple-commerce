---
title: "Using with AJAX"
---

Normally, you can use Simple Commerce's [form tags](/tags#form-tags) to build HTML `<form>` elements to do actions, such as adding to the cart or submitting a user's payment information during checkout.

However, sometimes you may want to use AJAX instead of forms as you don't need to wait for a page refresh after submitting data.

Submitting via AJAX is totally possible, it just takes a little bit more effort on your end when building your sites.

## Endpoints

Each of the [form tags](/tags#form-tags) point to different endpoints.

The easiest way to figure out which endpoint you want to use in place of a form tag would be to use the form tag temporarily in your template and grab the outputted form `action`.

If you're super duper interested, here's [the routes file](https://github.com/duncanmcclean/simple-commerce/blob/master/routes/actions.php), in case there's any 'hidden' routes that I've not written a tag for. (Spoiler alert: there's a few)

You can send any of the same parameters to the endpoints as documented in the respective tag.

Instead of returning redirects when you submit actions, a JSON response will be returned, containing the request status, a message and any assosiated resources, like the current cart.

```json
{
 	"status": "success",
  	"message": "Cart Updated",
  	"cart": {...}
}
```

## CSRF

CSRF is a feature of Laravel which essentially helps to prevent request spoofing by providing a token only available to the current user's specific session.

When making POST/DELETE requests to Simple Commerce endpoints, remember to provide a `_token` parameter with a CSRF token.

```js
let params = {
  _token: "{{ csrf_token }}",
};
```

## Examples

To get you started, here's two examples of making requests to Simple Commerce endpoints using `axios` and Javascript's native `fetch` API:

### `axios`

Here's a quick & basic example of using Axios to make an HTTP request to one of Simple Commerce's endpoints.

```js
let params = {
  _token: "{{ csrf_token }}",
  _request: 'Empty',
  '_error_redirect': 'Empty',
  '_redirect': 'Empty',
  product: "your-product-id",
  quantity: 1,
};

axios.post("/!/simple-commerce/cart-items", params).then((response) => {
  console.log("Woo hoo! The product has been added to your cart");
});
```

### `fetch`

Here's a quick example of using JavaScript's native `fetch` API to make an HTTP request to one of Simple Commerce's endpoints. Make sure you pass the `Accept` & `Content-Type` headers, otherwise you won't receive the expected response.

```js
let headers = {
  "Accept": "application/json",
  "Content-Type": "application/json",
};

let body = {
  _token: "{{ csrf_token }}",
  _request: 'Empty',
  '_error_redirect': 'Empty',
  '_redirect': 'Empty',
  product: "your-product-id",
  quantity: 1,
};

fetch("/!/simple-commerce/cart-items", {
  method: "POST",
  headers: headers,
  body: JSON.stringify(body),
}).then((response) => {
  console.log("Woo hoo! The product has been added to your cart");
});
```

## Form Parameter Validation

Simple Commerce expects three parameters to be included in every request:

- `_redirect`
- `_error_redirect`
- `_request`

When you use Simple Commerce's built-in tags to build the `<form>` elements, it takes the provided value and encrypts it to prevent tampering. 

However, when sending forms with AJAX, you're not always able to encrypt the values. In this case, you can disable the behaviour by adding the following setting to your configuration file:

```php
// config/simple-commerce

'disable_form_parameter_validation' => true,
```
