✨ Before getting started with Commerce, please take the time to read through this guide ✨

This is a guide for contributing to the Simple Commerce addon for Statamic.

# What you should know before contributing

Like Statamic itself, this Simple Commerce addon is open-source but it's **not free**. To use this addon in production, you **must** purchase it through the Statamic marketplace.

If you're looking for official help with this addon (and you have purchased this addon), then please [email us](mailto:hello@doublethree.digital). You may also ask for help on the [Statamic discord](https://statamic.com/disocrd) (use the #third-party channel).

To report a bug with this addon or request a feature, then please create an issue.

# How you can contribute

## Bug reports

Before opening an issue, please make sure that a similar one does not already exist. If you do find a similar issue, please use the 👍 emoji to upvote that you're having an issue. If you have any additional information not already in the issue, add a comment.

If no one has submitted an issue yet, please create your own one. Please fill all the required fields in on the issue template. Issues not created with the issue template will need to be re-done.

## Feature requests

If you have a feature request for the addon, please check if it already exists. If not please create a new issue using the correct issue template.

## Security disclosures

If you discover a security vulnrability with the addon, please [email us](mailto:duncan@doublethree.digital). We will review the issue and deal with it.

## Compiled assets

If you are submitting a change which makes updates to the addon's JavaScript, we would ask that you run `yarn run production` before submitting your pull request.

## Pull requests

Pull requests should clearly describe the problem and solution. We would also ask that you reference any issues numbers that the pull request affects. If you are adding new functionality or updating the way something works, we would also ask that you update the relevant tests.

# Setup Guide for contributors

If you're going to make contributions to Simple Commerce with writing code, fixing bugs etc, you'll want a separate install for that.

First, create a Statamic site which you'll use for Simple Commerce development.

```
composer create-project statamic/statamic simple-commerce-dev --prefer-dist --stability=dev
```

Then pull down the Simple Commerce repository (or your fork of it) to your project's folder.

```
git clone git@github.com:doublethreedigital/simple-commerce.git sp-source
```

You should have the Simple Commerce stuff in your `sp-source` folder. To install it in your Statamic site, open up it's `composer.json` file and add a few things.

The first thing to add is the `require` package thing:

```json
"require": {
	"doublethreedigital/simple-commerce": "dev-master"
},
```

And also add this repositories section, it'll tell Composer that we should load the package from our computer instead of downloading it from Packagist.

```json
"repositories": [
        {
            "type": "path",
            "url": "sp-source"
        }
]
```

And then just run `composer install` and Simple Commerce should be installed and you can work on your first pull request! 🥳
