<template>
    <div>
        <section class="bg-grey-20 rounded w-full mt-2">
            <table class="bg-white data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Rate</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rate in items" :key="rate.id">
                        <td>{{ rate.name }}</td>
                        <td>{{ rate.rate }}%</td>
                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" @click="updateTaxRate(rate)"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" :redirect="rate.deleteUrl"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center flex-row p-2">
                <button class="btn btn-primary" @click="createStackOpen = true">
                    Add Tax Rate
                </button>
            </div>
        </section>

        <create-stack
                v-if="createStackOpen"
                title="Create Tax Rate"
                :action="storeEndpoint"
                :blueprint="blueprint"
                :meta="meta"
                :values="values"
                @closed="createStackOpen = false"
                @saved="rateSaved"
        ></create-stack>

        <update-stack
                v-if="editStackOpen"
                title="Update Tax Rate"
                :action="editRate.updateUrl"
                :blueprint="blueprint"
                :meta="meta"
                :values="editRate"
                @closed="editStackOpen = false"
                @saved="rateUpdated"
        ></update-stack>
    </div>
</template>

<script>
    import axios from 'axios'
    import CreateStack from "../Stacks/CreateStack";
    import UpdateStack from "../Stacks/UpdateStack";

    export default {
        name: "TaxRate",

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
                editRate: [],

                createStackOpen: false,
                editStackOpen: false
            }
        },

        methods: {
            getRates() {
                axios.get(this.indexEndpoint)
                    .then(response => {
                        this.items = response.data;
                    }).catch(error => {
                    this.$toast.error(error);
                })
            },

            updateTaxRate(rate) {
                this.editRate = rate;
                this.editStackOpen = true;
            },

            rateSaved() {
                this.createStackOpen = false;
                this.getRates();
            },

            rateUpdated() {
                this.editStackOpen = false;
                this.getRates();
            },
        },

        mounted() {
            this.getRates();
        }
    }
</script>

<style scoped>

</style>
