# Customising

## Blueprints

Commerce gives you a few blueprints out of the box which are used to form the fields that can be used in the Control Panel. 

Sometimes, you may need to add your own fields to blueprints. It's actually really easy to do this because when you run the install script for Commerce, it'll automatically copy our blueprints to `resources/blueprints`. This means you can edit the blueprints the same way you can with your own.

> Whenever you update Commerce, you may need to re-add your custom fields because we may make updates to the blueprints.

## Routing

By default, Commerce gives you a [front-end boilerplate](./front-end.md). So you can see those front-end view we also register our [own routes](https://github.com/damcclean/commerce/blob/master/routes/web.php). But sometimes you may want to custimise how those routes look.

You can change the way your routes look by updating your site's `routes/web.php` file and point the routes to our controllers.

Bearing in mind, you'll also need to change the routes for the views of our front-end boilerplate as some of the URLs for `<form>` tags are hard coded in.
