---
title: Digital Products
---

Simple Commerce supports both physical and digital products.

When a product is classified as a **Digital Product**, customers will receive a link to download their purchased products after checkout. If you're selling software, Simple Commerce also generates license keys for each product so you can verify your purchases using a basic license verification API.

## Making a product "digital" & adding downloadable assets

By default, all products are classified as "Physical" products. However, you can easily flip the switch and change a product to a "Digital" product using the Product Type toggle:

![Product Type toggle](/img/simple-commerce/product-type-toggle.png)

After switching to a Digital Product, you'll see two fields appear in the sidebar:

* `Downloadable Assets` - The files the customer should be able to download after checkout.
* `Download Limit` - The maximum number of times a product can be downloaded. Leave blank for no limit.

If you're editing a product with [variants](/product-variants), these fields will instead show in the options for each of your variants.

## Notifications

If you'd like to send your customers an email notification after they've purchased digital products, add the following to your `config/simple-commerce.php` config file.

```php
'notifications' => [
    'digital_download_ready' => [
        \DuncanMcClean\SimpleCommerce\Notifications\DigitalDownloadsNotification::class => [
            'to' => 'customer',
        ],
    ],
],
```

[Learn more about Notifications](/notifications) in Simple Commerce.

### Customising the default view

If you wish to customise the default email view, you can publish it with this command.

```
php artisan vendor:publish --tag="sc-digital-products-views"
```

You'll then find the published views in your `resources/views/vendor/sc-digital-products` folder.

### Using your own notification

If you wish to have full control over the notification being used here, you may simply replace the class name.

## License Keys

Simple Commerce will automatically generate license keys for each digital product that is purchased. This is often useful when selling software, where you want customers to enter a valid license key before they can use your software.

## License Verification API

We've included a basic verification endpoint which you can use to check if a customer's license key is valid. Before you can use the endpoint, you'll need to [enable Statamic's REST API](https://statamic.dev/rest-api#enable-the-api).

Once enabled, you can simply make a POST request to `/!/simple-commerce/digital-products/verification` with a JSON body containing the license key you wish to verify.

```json
{
    "license_key": "IpebSuXft9Koio5GgP7TSRdtl"
}
```

A valid response will look like this:

```json
{
    "license_key": "IpebSuXft9Koio5GgP7TSRdtl",
    "valid": true
}
```

And an invalid one will be like this.

```json
{
    "license_key": "IpebSuXft9Koio5GgP7TSRdtl",
    "valid": false
}
```

## Overriding the license key generation logic

By default, we create a serial license key which you can give to your customers. However, you may want to customise where the code comes from or maybe you want to send it away to a third party service.

To do this, you can create your own license key repository which [implements the one provided by this addon](https://github.com/duncanmcclean/simple-commerce/blob/main/src/Contracts/LicenseKeyRepository.php).

To register your repository, you'll need to bind it to our `LicenseKey` facade. You can do this in your `AppServiceProvider`.

```php
$this->app->bind('LicenseKey', App\Repositories\LicenseKeyRepository::class);
```

## Download History

Every time a customer downloads a product, Simple Commerce keeps track of it. We store the timestamp & the customer's IP address for reference.

The download history log looks like this:

```yaml
items:
    - product: d113c964-d254-4f6b-931b-686348f36af5
      quantity: 1
      total: 9000
      id: a50a22d3-432f-4b6c-85db-48ea7ba92036
      license_key: COt2IXuPqP6VTg3cfXmqQmP0
      download_url: 'blahbla.test/link-for-download'
      download_history:
          - timestamp: 1613228831
            ip_address: 127.0.0.1
          - timestamp: 1613228828
            ip_address: 127.0.0.1
          - timestamp: 1613228746
            ip_address: 127.0.0.1
          - timestamp: 1613228722
            ip_address: 127.0.0.1
```
