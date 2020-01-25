<template>
    <div>
        <section class="bg-grey-20 rounded w-full mt-2">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Primary?</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="status in items" :key="status.id">
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="'bg-'+status.color"></div>
                                {{ status.name }}
                            </div>
                        </td>

                        <td>{{ status.slug }}</td>

                        <td>{{ status.description }}</td>

                        <td v-if="status.primary === 'true'">Yes</td>
                        <td v-else>No</td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="#"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" :redirect="status.deleteUrl"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center flex-row p-2">
                <button class="btn btn-primary" @click="createStackOpen = true">
                    Create Product
                </button>
            </div>
        </section>

        <stack
            v-if="createStackOpen"
            name="create-order-status"
            @closed="createStackOpen = false"
        >
            <publish-form
                title="Create Customer"
                :action="meta.store"
                :blueprint='meta.blueprint'
                :meta='meta.meta'
                :values='meta.values'
                @saved="statusSaved"
            ></publish-form>
        </stack>
    </div>
</template>

<script>
    import axios from 'axios'

    export default {
        name: "OrderStatusSettingsFieldtype",

        mixins: [Fieldtype],

        props: [
            'meta', 'value'
        ],

        data() {
            return {
                items: [],

                createStackOpen: false,
            }
        },

        methods: {
            getStatuses() {
                axios.get(this.meta.index)
                    .then(response => {
                        this.items = response.data;
                    }).catch(error => {
                        this.$toast.error(error);
                    })
            },

            statusSaved() {
                this.createStackOpen = false;
                this.$toast.success('Created order status');
                this.getStatuses();
            },

            updateStatus(status) {
                //
            },
        },

        mounted() {
            this.getStatuses();
        }
    }
</script>

<style scoped>

</style>
