# Custom Attributes
Simple Commerce allows you to add your own fields to the create/edit screens of products and variants. We call this feature, Attributes.

## Setting up custom attributes
To setup your own custom attributes, go to `Fields > Fieldsets` in the Control Panel. You'll then see two fieldsets, one for product attributes and one for variant attributes.

By default, they will be empty but you can just add fields to them like you can any other fieldset or blueprint. And if by magic, they'll display inside your Control Panel pages.

## Using them in templates
Your custom attributes are treated like a first class citizen in your templates. They come included whenever you're requesting products and variants in your front-end.

Here's an example, where `review_text` is our custom attribute.

```
<h1>{{ title }}</h1>
<p>{{ review_text }}</p>
```
