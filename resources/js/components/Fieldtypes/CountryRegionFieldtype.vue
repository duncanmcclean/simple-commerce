<template>
    <div v-if="regions.length > 0">
        <Select
            class="w-full"
            :options="regions"
            :model-value="selectedRegion"
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

    inject: ['store'],

    mounted() {
        if (this.store.values.country) {
            this.fetchRegions(this.store.values.country);
        }

        this.store.$subscribe((mutation, state) => {
            if (mutation.events?.key === 'country') {
                if (mutation.events.newValue !== mutations.events.oldValue) {
                    this.fetchRegions(mutation.events.newValue);
                }
            }
        });
    },

    computed: {
        selectedRegion() {
            return this.value?.id;
        },
    },

    methods: {
        fetchRegions(country) {
            console.log('getting regions')
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

        regionSelected(region) {
            this.update(region.id);
        },
    },
}
</script>
