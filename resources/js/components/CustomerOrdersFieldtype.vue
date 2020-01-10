<template>
    <div>
        <section class="bg-grey-20 rounded w-full mt-2">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="order in meta" :key="order.id">
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="'bg-'+order.order_status.color"></div>
                                <a href="#">Order #{{ order.id }}</a>
                            </div>
                        </td>

                        <td>
                            {{ order.created_at }}
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="#"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="#"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</template>

<script>
    export default {
        name: "CustomerOrdersFieldtype",

        mixins: [Fieldtype],

        props: [
            'meta', 'value',
        ],

        //
    }
</script>

<style scoped>

</style>
