<template>
    <div>
        <ul class="list-disc ltr:pl-3">
            <li v-if="values.type === 'fixed' && values.value && values.value.value !== null" class="text-sm mb-1.5">
                <span class="font-semibold" v-text="formatCurrency(values.value.value)"></span> off entire order
            </li>

            <li v-if="values.type === 'percentage' && values.value && values.value.value !== null" class="text-sm mb-1.5">
                <span class="font-semibold" v-text="`${values.value.value}%`"></span> off entire order
            </li>

            <li v-if="values.minimum_cart_value" class="text-sm mb-1.5">
                Redeemable when items total is above <span v-text="formatCurrency(this.values.minimum_cart_value)"></span>
            </li>

            <li v-if="values.customer_eligibility === 'all'" class="text-sm mb-1.5">
                {{ __(`Redeemable by all customers`) }}
            </li>

            <li v-if="values.customer_eligibility === 'specific_customers'" class="text-sm mb-1.5">
                Only redeemable by specific customers
            </li>

            <li v-if="values.maximum_uses" class="text-sm mb-1.5">
                Can only be used {{ values.maximum_uses }} times
            </li>

            <li v-if="values.products.length > 0" class="text-sm mb-1.5">
                Can only be used when certain products are part of the order
            </li>

            <li v-if="values.valid_from?.date" class="text-sm mb-1.5">
                Redeemable after {{ values.valid_from.date }}
            </li>

            <li v-if="values.expires_at?.date" class="text-sm mb-1.5">
                Redeemable until {{ values.expires_at.date }}
            </li>
        </ul>
    </div>
</template>

<script>
import { __ } from '../../../../vendor/statamic/cms/resources/js/bootstrap/globals'

export default {
    name: 'CouponSummaryFieldtype',

    mixins: [Fieldtype],

    inject: ['storeName'],

    computed: {
        values() {
            return Statamic.$store.state.publish[this.storeName].values
        },
    },

    methods: {
        formatCurrency(amount) {
            return this.meta.currency.symbol + amount
        },
    }
}
</script>
