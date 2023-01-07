<template>
    <div class="form-group w-full">
        <label class="block mb-1">{{ __('Region') }}</label>
        <select v-model="region" class="input-text">
            <option value="" selected disabled="true">
                {{ __('Please select') }}
            </option>
            <option
                v-for="region in regions"
                :key="region.id"
                :value="region.id"
                v-text="region.name"
            ></option>
        </select>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    props: {
        value: {
            type: String,
            required: false,
        },
    },

    data() {
        return {
            busy: true,
            regions: [],
            region: null,
        }
    },

    mounted() {
        let countryInput = document.getElementsByName('country')[0]

        this.fetchRegions(countryInput.value)

        if (this.value) {
            this.region = this.value
        }

        countryInput.addEventListener('change', e => {
            this.region = null
            this.fetchRegions(countryInput.value)
        })
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
                    this.regions = response.data
                })
                .catch(error => {
                    this.$toast.error(`There was an error fectching regions.`)
                })
        },
    },

    watch: {
        region(value) {
            document.getElementById('regionInput').value = value
        },
    },
}
</script>
