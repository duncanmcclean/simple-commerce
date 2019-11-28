## eCommerce for Statamic

An eCommerce addon for Statamic v3.

### Installation

```
$ composer require damcclean/commerce
```

* Then publish config from service provider
* Then publish blueprints to `resources/blueprints`

### To Do

* [x] Control Panel (CRUD interface)
* [x] Example front-end shop
* [x] Order processing (Stripe stuff)
* [ ] Notifications
* [ ] Fix PaymentMethod & PaymentIntent issues (in the checkout flow)
* [ ] Store Dashboard
* [ ] Use the stache to cache content
* [ ] Widgets
* [ ] Customise the routing
* [ ] Split address into multiple fields
* [x] Fix search on listings
* [ ] Receipts
* [ ] Events
* [x] Install command
* [ ] Get front-end assets in a way they can be published
* [ ] Fix issue after saving assets from publish form (not being formatted properly in yaml)
* [ ] If product is deleted while in cart then the user will get error
* [ ] Dont allow users to add out of stock items to their cart
* [ ] Make sure Strong Customer Authentication is implemented correctly
* [ ] Clear cart button in checkout
* [ ] Don't show the checkout flow if there are no items to buy
* [ ] Make use of action routes for front-end post stuff
* [ ] Stripe webhooks
* [ ] Product variations
* [ ] Translation
* [ ] Tax and shipping
* [ ] Documentation
* [ ] 🚀 Launch for beta testers!

### Addon Dev Questions

* How can I add my own SVG icons for the Control Panel nav or is there an icon pack I can choose from?
* How can I fix the `A facade root has not been set.` issue with the Yaml facade?
* Can I use the stache to cache stuff in my addon?
