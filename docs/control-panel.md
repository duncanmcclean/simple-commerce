---
title: Control Panel
---

## Overview

TODO: screenshot

The 'Overview' page gives you and your users a quick overview of your e-commerce store.

By default, all 'widgets' will be visible to you. If you wish you hide a certain widget, you may do so with the 'Configure' button at the top right of the page. Your preferences will be saved for future.

### Registering a custom 'overview widget'

You may build your own Overview Widgets. There's two parts of a widget: backend & frontend.

### Back-end

To register an overview widget, simply add something like this to your `AppServiceProvider` (or similar):

```php
static::registerWidget(
    'orders-chart',
    [
        'name' => 'Orders Chart',
        'component' => 'overview-orders-chart',
    ],
    function (Request $request) {
        return [];
    }
);
```

The first parameter should be the 'handle' of the widget, the second parameter should be an array which contains both a `name` and a `component`. Finally, the last parameter should be a 'callback' which will be used to gather 'data' for your widget.

### Front-end

After you've got your back-end sorted for your widget, you'll need to get the front-end sorted. The front-end requires you to setup a Vue component which is loaded in the Control Panel (you may read about how to do this over on [the Statamic docs](https://statamic.dev/extending/control-panel#adding-css-and-js-assets)).

A blank widget component may look something like this:

```vue
<template>
  <div class="flex-1 card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2 pb-1">
      <h2>
        <span>Basic Widget</span>
      </h2>
    </div>

    <p>Hello world!</p>
  </div>
</template>

<script>
export default {
  props: {
    data: Array,
  },
};
</script>
```

The `data` prop will be what's returned from your PHP callback.
