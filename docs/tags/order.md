---
title: Order Tag
---

## Receipt URL

If you need to get the Receipt URL for a specific order, here's a real easy way of doing this:

```antlers
<a href="{{ sc:order:receiptUrl order="one-two-three" }}" target="_blank">Download receipt</a>
```

You're required to pass in the ID of the order as the `order` parameter. The tag will output a URL.
