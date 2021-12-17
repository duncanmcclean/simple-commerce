---
title: Countries
---

This tag lets you loop through countries.

```antlers
{{ sc:countries }}
  <option value="{{ iso }}">{{ name }}</option>
{{ /sc:countries }}
```

When looping through, you may also loop through the country's related regions, like so:

```antlers
{{ sc:countries }}
  <h2>{{ name }}</h2>
  <ul>
    {{ regions }}
        <li>{{ name }}</li>
    {{ /regions }}
  </ul>
{{ /sc:countries }}
```

## Parameters

### only

For example:

```
{{ sc:countries only="GB|IE" }}
```

This will only return countries passed and in the order they are passed. It accepts a piped list of either iso values or country names.


### exclude

For example:

```
{{ sc:countries exclude="GB|IE" }}
```

This will exclude any countries passed from the list. It Accepts a piped list of either iso values or country names. This param has no effect when the `only` param is used.


### common

For example:

```
{{ sc:countries common="GB|IE" }}
```

This will creates a 'common' countries list above the rest of the country list, allowing you to make it eaiser to get to the most common countries your visitors select. It accepts a piped list of either iso values or country names. This param has no effect when the `only` param is used.
