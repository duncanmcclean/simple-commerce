# Modifiers

::: v-pre

Simple Commerce also provides a few Antler modifiers to help output data in useful ways.

## Price
This modifier converts a number like `15` to being currency formatted, like `$15.00`. It'll use you're stores currency settings to format the output.

```html
{{ from_price | price }}
```

:::