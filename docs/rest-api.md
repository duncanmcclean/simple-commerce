# REST API

> Currently the REST API in Simple Commerce is read only. But once Statamic has support for API authentication, we'll start building in a write API.

We've built a REST API for Simple Commerce, on top of Statamic's Content API that comes out the box with Statamic 3. This means that to use our API, you'll need to have [enabled Statamic's Content API](https://statamic.dev/rest-api#enable-the-api).

## Products

### `GET` `/api/simple-commerce/products`
Returns an index of products from your store.

```json
{
  "data": {...},
  "links": {
    "first": "http://yourstore.test/api/simple-commerce/products?page=1",
    "last": "http://yourstore.test/api/simple-commerce/products?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://yourstore.test/api/simple-commerce/products",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

### `GET` `/api/simple-commerce/products/{product}`
Coming Soon

## Product Categories

### `GET` `/api/simple-commerce/product-categories`
Returns an index of product categories from your store.

```json
{
  "data": {...},
  "links": {
    "first": "http://yourstore.test/api/simple-commerce/product-categories?page=1",
    "last": "http://yourstore.test/api/simple-commerce/product-categories?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://yourstore.test/api/simple-commerce/product-categories",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

### `GET` `/api/simple-commerce/product-categories/{category}`
Coming Soon
