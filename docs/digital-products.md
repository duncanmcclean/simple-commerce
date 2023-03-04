---
title: Digital Products
---

Simple Commerce has an additional Digital Products addon, allowing you to link up assets that your customer will be able to download after checking out. If you need it, the addon also includes a basic license verification API.

## Installation

To install, require this addon as a Composer dependency:

```
composer require doublethreedigital/sc-digital-products
```

If you wish, you may also publish the configuration file for this addon by running the following command: `php artisan vendor:publish --tag="sc-digital-products-config"`. The config file will exist as `config/sc-digital-products.php`.

**That's it!** You're ready to start selling digital products! 🎉

## Adding downloadable assets to products

Once installed, you'll see a `Digital Product` tab appear on the publish form for your product entries.

![Screenshot](https://raw.githubusercontent.com/duncanmcclean/sc-digital-products/master/publish-form.png)

In each of your digital products, you should enable the `Is Digital Product?` toggle and add the downloadable assets. These are the assets the customer will be able to access once they have purchased your product.

### Product Variants

As of v3.1.0, the Digital Products addon supports adding different 'downloadable assets' per variant. So, instead of specifying the 'Download Limit' and 'Downloadable Assets' in the `Digital Product` tab, you'll specify them on each of your variants.

## Overriding the licence key generation logic

By default, we create a serial license key which you can give to your customers. However, you may want to customise where the code comes from or maybe you want to send it away to a third party service.

To do this, you can create your own license key repository which [implements the one provided by this addon](https://github.com/doublethreedigital/sc-digital-products/blob/master/src/Contracts/LicenseKeyRepository.php).

To register your repository, you'll need to bind it to our `LicenseKey` facade. You can do this in your `AppServiceProvider`.

```php
$this->app->bind('LicenseKey', App\Repositories\LicenseKeyRepository::class);
```

## Notifications

If you'd like the Digital Products addon to send your customers an email notification after they've purchased digital products, add the following to your `config/simple-commerce.php` config file.

```php
'notifications' => [
    'digital_download_ready' => [
        \DoubleThreeDigital\DigitalProducts\Notifications\DigitalDownloadsNotification::class => [
            'to' => 'customer',
        ],
    ],
],
```

You can learn more about Simple Commerce's notifications system in the [SC Documentation](https://simple-commerce.duncanmcclean.com/notifications).

### Customising the default view

If you wish to customise the default email view, you can publish it with this command.

```
php artisan vendor:pulish --tag="sc-digital-products-views"
```

You'll then find the published views in your `resources/views/vendor/sc-digital-products` folder.

### Using your own notification

If you wish to have full control over the notification being used here, you may simply replace the class name.

## License Verification

We've included a basic verification endpoint which you can use to check if a customer's license key is valid. Before you can use the endpoint, you'll need to [enable Statamic's REST API](https://statamic.dev/rest-api#enable-the-api).

Once enabled, you can simply make a POST request to `/api/sc-digital-downloads/verification` with a JSON body containing the license key you wish to verify.

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

## Download History

The Digital Downloads addon automatically provides a history log of every time something is downloaded. The download history is tracked per order item. When tracking downloads, we store the timestamp of download and the IP address it was downloaded from.

Inside your order's entry, it may look something like this:

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
