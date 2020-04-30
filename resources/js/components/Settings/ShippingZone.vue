<template>
    <div>
        <section class="bg-grey-20 rounded w-full mt-2">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Countries</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="zone in items" :key="zone.id">
                        <td>{{ zone.name }}</td>
                        <td>{{ zone.listOfCountries }}</td>
                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" @click="updateShippingZone(zone)"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" @click="deleteShippingZone(zone)"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center flex-row p-2">
                <button class="btn btn-primary" @click="createStackOpen = true">
                    Add Shipping Zone
                </button>
            </div>
        </section>

        <create-stack
                v-if="createStackOpen"
                title="Create Shipping Zone"
                :action="storeEndpoint"
                :blueprint="blueprint"
                :meta="meta"
                :values="values"
                @closed="createStackOpen = false"
                @saved="zoneSaved"
        ></create-stack>

        <update-stack
                v-if="editStackOpen"
                title="Update Shipping Zone"
                :action="editZoneUpdateUrl"
                :blueprint="blueprint"
                :meta="meta"
                :values="editZone"
                @closed="editStackOpen = false"
                @saved="zoneUpdated"
        ></update-stack>
    </div>
</template>

<script>
    import axios from 'axios'
    import CreateStack from "../Stacks/CreateStack";
    import UpdateStack from "../Stacks/UpdateStack";

    export default {
        name: "ShippingZone",

        components: {
            CreateStack,
            UpdateStack
        },

        props: {
            indexEndpoint: String,
            storeEndpoint: String,
            initialBlueprint: Array,
            initialMeta: Array,
            initialValues: Array
        },

        data() {
            return {
                blueprint: JSON.parse(this.initialBlueprint),
                meta: JSON.parse(this.initialMeta),
                values: JSON.parse(this.initialValues),

                items: [],
                editZone: [],
                editZoneUpdateUrl: '',

                createStackOpen: false,
                editStackOpen: false
            }
        },

        methods: {
            getZones() {
                axios.get(this.indexEndpoint)
                    .then(response => {
                        this.items = response.data;
                    }).catch(error => {
                        this.$toast.error(error);
                    });
            },

            deleteShippingZone(zone) {
                axios.delete(zone.deleteUrl)
                    .then(response => {
                        this.getZones();
                    })
                    .catch(error => {
                        this.$toast.error(error);
                    });
            },

            updateShippingZone(zone) {  
                axios.get(zone.editUrl)
                    .then(response => {
                        this.editZone = response.data.values;
                        this.editZoneUpdateUrl = response.data.action;
                        this.editStackOpen = true;
                    })
                    .catch(error => {
                        alert('Something happened: '+ error);
                    })
            },

            zoneSaved() {
                this.createStackOpen = false;
                this.getZones();
            },

            zoneUpdated() {
                this.editStackOpen = false;
                this.getZones();
            },
        },

        mounted() {
            this.getZones();
        }
    }
</script>

<style scoped>

</style>
