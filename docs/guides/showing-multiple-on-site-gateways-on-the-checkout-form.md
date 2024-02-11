---
title: Showing multiple on-site gateways on the Checkout form
---

If you want to give the customer a choice of how they want to pay (bank transfer or credit card, for example), you can offer the customer a choice of payment methods by looping through the `{{ sc:gateways }}` tag.

In the below example, we're using Alpine.js to react to the value of the `<select>` element. We'd also recommend splitting the payment forms into their own partials.

```antlers
<div x-data="{ gateway: '{{ sc:gateways }}{{ if first }}{{ formatted_class }}{{ /if }}{{ /sc:gateways }}' }">
	<h2>Secure Payment</h2>

	<select
    	x-model="gateway"
        class="h-10 w-full border rounded p-2 mb-2 outline-none mr-2"
        name="gateway"
    >
		{{ sc:gateways }}
			<option value="{{ class }}">{{ name }}</option>
		{{ /sc:gateways }}
	</select>

    {{ sc:gateways }}
		<div x-show="gateway === '{{ formatted_class }}'">
			<!-- Payment form partial -->
		</div>
	{{ /sc:gateways }}

    <button
    	type="button"
        @click.prevent="if(typeof confirmPayment == 'function' && document.getElementsByName('gateway')[0].value == 'DuncanMcClean\\SimpleCommerce\\Gateways\\Builtin\\StripeGateway') { confirmPayment(); } else { document.querySelector('form').submit() }">
        Complete Checkout
	</button>
</div>
```
