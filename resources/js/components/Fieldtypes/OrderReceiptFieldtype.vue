<template>
    <div>
        <div class="receipt-table w-full">
            <div class="receipt-table-header">
                <div class="col-span-3">Product</div>
                <div class="text-right">Unit Price</div>
                <div class="text-right">Quantity</div>
                <div class="text-right">Total</div>
            </div>
            <div class="receipt-line-item" v-for="lineItem in value.line_items">
                <div>
                    <!-- TODO: Open product in stack -->
                    <!-- TODO: Support for product variants -->
                    <a :href="lineItem.product.edit_url" target="_blank">
                        {{ lineItem.product.title }}
                    </a>
                </div>
                <div>{{ lineItem.unit_price }}</div>
                <div>{{ lineItem.quantity }}</div>
                <div>{{ lineItem.total }}</div>
            </div>
            <div class="receipt-total font-semibold border-t dark:border-dark-500">
                <div>Subtotal</div>
                <div>{{ value.totals.sub_total }}</div>
            </div>
            <div v-if="value.discount" class="receipt-total">
                <div>
                    <span>Coupon Discount (COUPONCODE)</span>
                    <span class="help-block">50% off</span>
                </div>
                <div>{{ value.totals.discount_total }}</div>
            </div>
            <div v-if="value.shipping" class="receipt-total">
                <div>
                    <span>Shipping</span>
                    <span class="help-block">Royal Mail</span>
                </div>
                <div>{{ value.totals.shipping_total }}</div>
            </div>
            <div class="receipt-total">
                <div>Taxes</div>
                <div>{{ value.totals.tax_total }}</div>
            </div>
            <div class="receipt-total font-bold text-lg">
                <div>Grand Total</div>
                <div>{{ value.totals.grand_total }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    inject: ['storeName'],
}
</script>