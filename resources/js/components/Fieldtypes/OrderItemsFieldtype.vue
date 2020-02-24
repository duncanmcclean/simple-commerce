<template>
    <div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in value.items">
                    <td><a :href="cp_url('/products/edit/'+item.product.uuid)">{{ item.product.title }} ({{ item.variant.name }})</a></td>
                    <td>{{ item.quantity }}</td>
                    <td>{{ item.formatted_total }}</td>
                </tr>

                <!-- TODO: this tax total is a bit off -->
                <tr v-for="tax in value.tax">
                    <td>{{ tax.tax_rate.name }}</td>
                    <td></td>
                    <td>{{ value.totals.tax }}</td>
                </tr>

                <tr v-for="shipping in value.shipping">
                    <!-- TODO: need a better system for shipping zone naming, and add the shipping zone state (if it has one) -->
                    <td>{{ shipping.shipping_zone.country.name }}, {{ shipping.shipping_zone.start_of_zip_code }}</td>
                    <td></td>
                    <td>{{ value.totals.shipping }}</td>
                </tr>

                <tr>
                    <td><strong>Overall Total</strong></td>
                    <td></td>
                    <td>{{ value.totals.overall }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: "OrderItemsFieldtype",

        mixins: [Fieldtype],

        props: [
            'meta', 'value'
        ],
    }
</script>

<style scoped>

</style>
