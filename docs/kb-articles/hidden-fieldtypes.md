---
title: Hidden Fieldtypes
---

For various reasons, Simple Commerce includes a few 'hidden fieldtypes' that you may see in the Fieldtype selector when editing blueprints/fieldsets.

Long story short: Simple Commerce needs these to inject its own 'variables' into entries which you can then take advantage of in Antlers.

A good example of this is the 'SC: Receipt URL' fieldtype. It's automatically added to order entries (without you even noticing, most of the time), to let you do this when looping through orders.

```antlers
{{ sc:cart }}
    <a href="{{ receipt_url }}">Download receipt</a>
{{ /sc:cart }}
```

These fieldtypes won't be visible in the Control Panel or save anything in your entry's file, literally just used for display purposes.
