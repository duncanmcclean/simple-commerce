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

                        <td v-if="status.primary === true">Yes</td>
                        <td v-else>No</td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Make Primary" @click="makePrimary(status)"></dropdown-item>
                                <dropdown-item text="Edit" @click="updateStatus(status)"></dropdown-item>
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

        <create-order-status-stack
            v-if="createStackOpen"
            :action="meta.store"
            :blueprint="meta.blueprint"
            :meta="meta.meta"
            :values="meta.values"
            @closed="createStackOpen = false"
            @saved="statusSaved"
        ></create-order-status-stack>

        <update-order-status-stack
            v-if="editStackOpen"
            :action="editStatus.updateUrl"
            :blueprint="meta.blueprint"
            :meta="meta.meta"
            :values="editStatus"
            @closed="editStackOpen = false"
            @saved="statusUpdated"
        ></update-order-status-stack>
    </div>
</template>

<script>
    import axios from 'axios'
    import CreateOrderStatusStack from "../Stacks/CreateOrderStatusStack";
    import UpdateOrderStatusStack from "../Stacks/UpdateOrderStatusStack";

    export default {
        name: "OrderStatusSettingsFieldtype",

        components: {
            CreateOrderStatusStack,
            UpdateOrderStatusStack
        },

        mixins: [Fieldtype],

        props: [
            'meta', 'value'
        ],

        data() {
            return {
                items: [],
                editStatus: [],

                createStackOpen: false,
                editStackOpen: false
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

            makePrimary(status) {
                axios.post(status.updateUrl, {
                    name: status.name,
                    slug: status.slug,
                    description: status.description,
                    color: status.color,
                    primary: true
                }).then(response => {
                    this.$toast.success(status.name + ' is now the primary order status');
                    this.getStatuses();
                }).catch(error => {
                    this.$toast.error(error);
                });
            },

            updateStatus(status) {
                this.editStatus = status;
                this.editStackOpen = true;
            },

            statusSaved() {
                this.createStackOpen = false;
                this.getStatuses();
            },

            statusUpdated() {
                this.editStackOpen = false;
                this.getStatuses();
            },
        },

        mounted() {
            this.getStatuses();
        }
    }
</script>

<style scoped>

</style>
