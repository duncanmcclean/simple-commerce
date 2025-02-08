<template>
    <div>
        <div class="receipt-table w-full">
            <div class="receipt-table-header">
                <div class="col-span-3 text-sm">{{ __('Product') }}</div>
                <div class="text-right text-sm">{{ __('Unit Price') }}</div>
                <div class="text-right text-sm">{{ __('Quantity') }}</div>
                <div class="text-right text-sm">{{ __('Total') }}</div>
            </div>
            <LineItem
                v-for="lineItem in receipt.line_items"
                :lineItem="lineItem"
                :key="lineItem.id"
                :form-component="meta.product.formComponent"
                :form-component-props="meta.product.formComponentProps"
                @updated="lineItemUpdated"
            />
            <div class="receipt-total font-semibold border-t dark:border-dark-500">
                <div>{{ __('Subtotal') }}</div>
                <div>{{ receipt.totals.sub_total }}</div>
            </div>
            <div v-if="receipt.coupon" class="receipt-total">
                <div>
                    <span>{{ __('Coupon Discount (:code)', {code: receipt.coupon.code}) }}</span>
                    <span class="help-block mb-0">{{ receipt.coupon.discount }}</span>
                </div>
                <div>-{{ receipt.totals.discount_total }}</div>
            </div>
            <div v-if="receipt.shipping" class="receipt-total">
                <div>
                    <span>{{ __('Shipping') }}</span>
                    <span class="help-block mb-0">{{ receipt.shipping.name }}</span>
                </div>
                <div>{{ receipt.shipping.price }}</div>
            </div>
            <div v-if="receipt.taxes" class="receipt-total">
                <div>
                    <span>{{ __('Taxes') }}</span>
                    <span v-for="item in receipt.taxes.breakdown" class="help-block mb-0">{{ item.rate }}% {{ item.description }} ({{ item.amount }})</span>
                </div>
                <div>{{ receipt.totals.tax_total }}</div>
            </div>
            <div class="receipt-total font-bold">
                <div>{{ __('Grand Total') }}</div>
                <div>{{ receipt.totals.grand_total }}</div>
            </div>
            <div v-if="receipt.refund.issued" class="receipt-total">
                <div>{{ __('Refund') }}</div>
                <div>-{{ receipt.totals.amount_refunded }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import LineItem from './OrderReceipt/LineItem.vue'

export default {
    components: { LineItem },

    mixins: [Fieldtype],

    data() {
        return {
            receipt: this.value,
        }
    },

    methods: {
        lineItemUpdated(lineItem) {
            this.receipt.line_items = this.receipt.line_items.map(item => {
                if (item.id === lineItem.id) {
                    return lineItem
                }

                return item
            })
        },
    },
}
</script>