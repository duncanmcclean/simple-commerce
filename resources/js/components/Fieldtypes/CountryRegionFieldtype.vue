<template>
    <div v-if="regions.length > 0">
        <Select
            class="w-full"
            :options="regions"
            :model-value="selectedRegion"
            option-value="id"
            option-label="name"
            @update:modelValue="regionSelected"
        />
    </div>
    <p v-else v-text="regions.length"></p>
</template>

<script>
import axios from 'axios'
import { FieldtypeMixin } from 'statamic'
import { Select } from '@statamic/ui'

export default {
    mixins: [FieldtypeMixin],

    components: { Select },

    props: {
        value: String,
    },

    data() {
        return {
            regions: [],
        }
    },

    mounted() {
        if (this.publishContainer.values.country) {
            this.fetchRegions(this.publishContainer.values.country);
        }
    },

    computed: {
        country() {
            return this.publishContainer.values.country;
        },

        selectedRegion() {
            return this.value?.id;
        },
    },

    methods: {
        fetchRegions(country) {
            axios
                .get(
                    cp_url(
                        `/simple-commerce/fieldtype-api/regions?country=${country}`
                    )
                )
                .then(response => {
                    this.regions = Object.values(response.data)
                })
                .catch(error => {
                    this.$toast.error(`There was an error fetching regions.`)
                })
        },

        regionSelected(regionId) {
            this.update(this.regions.filter(region => region.id === regionId)[0]);
        },
    },

    watch: {
        country(country) {
            this.fetchRegions(country);
        },
    },
}
</script>
