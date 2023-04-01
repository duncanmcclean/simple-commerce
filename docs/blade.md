---
title: Blade
---

Although Simple Commerce has been built to primarily support Antlers, you can pretty easily use its [Antlers Tags](/tags) in Blade.

> If you're using Blade, I'm guessing you've already seen [Statamic's Blade documentation](https://statamic.dev/blade#content).

## Using Tags in Blade

For some of Simple Commerce's tags which just return text (like `{{ sc:cart:grandTotal }}`), you can use it like this in your Blade view:

```blade
{{ Statamic::tag('sc:cart:grandTotal') }}
```

If you need to pass parameters to tags, you can provide the `param` parameter.

```
{{ Statamic::tag('sc:cart:update')->param('redirect', '/checkout') }}
```

Simple Commerce includes a concept of "form tags" which work slightly differently in Blade.

Instead of an HTML `<form>` being constructed & output for you, you need to construct the `<form>` yourself. Thankfully, it's easy to do:

```blade
@php
$form = Statamic::tag('sc:cart:addItem')->params([
    'redirect' => '/cart',
])->fetch();
@endphp

<form {!! $form['attrs_html'] !!}>
    {!! $form['params_html'] !!}

    <input type="hidden" name="product" value="{{ $id }}" />
    <input type="text" name="quantity" value="1" />
    <button>Add to Cart</button>
</form>
```

The above example is using the `{{ sc:cart:addItem }}` form. 

You simply use the `{!! $form['attrs_html'] !!}` in the `<form>` tag (this adds the `action` and `method` attributes). 

Then, somewhere inside the form, add the hidden parameters with `{!! $form['params_html'] !!}`.