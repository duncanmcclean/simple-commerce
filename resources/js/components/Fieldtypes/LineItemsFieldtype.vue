<template>
    <div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in value.items">
                    <td><a :href="cp_url('/products/edit/'+item.variant.product.uuid)">{{ item.variant.product.title }} ({{ item.variant.name }})</a></td>
                    <td>{{ item.quantity }}</td>
                    <td class="text-right">{{ item.formatted_total }}</td>
                </tr>
                <tr>
                    <td>Items Total</td>
                    <td></td>
                    <td class="text-right">{{ value.totals.items }}</td>
                </tr>
                <tr>
                    <td>Tax Total</td>
                    <td></td>
                    <td class="text-right">{{ value.totals.tax }}</td>
                </tr>
                <tr>
                    <td>Shipping Total</td>
                    <td></td>
                    <td class="text-right">{{ value.totals.shipping }}</td>
                </tr>
                <tr>
                    <td><strong>Overall Total</strong></td>
                    <td></td>
                    <td class="text-right">{{ value.totals.overall }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: "LineItemsFieldtype",

        mixins: [Fieldtype],

        props: [
            'meta', 'value'
        ],
    }
</script>

<style scoped>

</style>
