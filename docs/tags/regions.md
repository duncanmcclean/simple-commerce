---
title: Regions
---

This tag lets you loop through regions:

```antlers
{{ sc:regions }}
    <option value="{{ id }}">{{ name }} ({{ country:name }})</option>
{{ /sc:regions }}
```

When looping through, you may also reference the `name` & `iso` of the related country.

## Parameters

### country

You may scope down regions to a specific country if needed:

```antlers
{{ sc:regions country="GB" }}
```
