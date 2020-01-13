<template>
    <div>
        <section class="bg-grey-20 rounded w-full mt-2">
            <table v-if="hasItems" class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="order in orders" :key="order.id">
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="'bg-'+order.order_status.color"></div>
                                <a :href="order.edit_url">Order #{{ order.id }}</a>
                            </div>
                        </td>

                        <td>
                            {{ order.created_at }}
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" :redirect="order.edit_url"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" :redirect="order.delete_url"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="mx-2 my-4">This customer has not ordered anything yet.</p>
        </section>
    </div>
</template>

<script>
    import axios from 'axios'

    export default {
        name: "CustomerOrdersFieldtype",

        mixins: [Fieldtype],

        props: [
            'meta', 'value',
        ],

        data() {
            return {
                orders: [],
                hasItems: false,
            }
        },

        mounted() {
            if (window.customerId) {
                axios.post(this.meta, {
                    customer: window.customerId
                }).then(response => {
                    this.orders = response.data;
                    this.hasItems = true;
                }).catch(error => {
                    this.$toast.error(error);
                })
            }
        }
    }
</script>

<style scoped>

</style>
