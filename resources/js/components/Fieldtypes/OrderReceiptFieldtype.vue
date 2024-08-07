<template>
    <div>
        <div class="receipt-table w-full">
            <div class="receipt-table-header">
                <div class="col-span-3">Product</div>
                <div class="text-right">Unit Price</div>
                <div class="text-right">Quantity</div>
                <div class="text-right">Total</div>
            </div>
            <LineItem
                v-for="lineItem in receipt.line_items"
                :lineItem="lineItem"
                :key="lineItem.id"
                :form-component="meta.product.formComponent"
                :form-component-props="meta.product.formComponentProps"
                @updated="lineItemUpdated" />
            <div class="receipt-total font-semibold border-t dark:border-dark-500">
                <div>Subtotal</div>
                <div>{{ receipt.totals.sub_total }}</div>
            </div>
            <div v-if="receipt.discount" class="receipt-total">
                <div>
                    <span>Coupon Discount (COUPONCODE)</span>
                    <span class="help-block">50% off</span>
                </div>
                <div>{{ receipt.totals.discount_total }}</div>
            </div>
            <div v-if="receipt.shipping" class="receipt-total">
                <div>
                    <span>Shipping</span>
                    <span class="help-block">Royal Mail</span>
                </div>
                <div>{{ receipt.totals.shipping_total }}</div>
            </div>
            <div class="receipt-total">
                <div>Taxes</div>
                <div>{{ receipt.totals.tax_total }}</div>
            </div>
            <div class="receipt-total font-bold">
                <div>Grand Total</div>
                <div>{{ receipt.totals.grand_total }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import LineItem from './OrderReceipt/LineItem.vue'

export default {
    components: { LineItem },

    mixins: [Fieldtype],

    inject: ['storeName'],

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