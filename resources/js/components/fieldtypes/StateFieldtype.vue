<template>
    <div>
        <!-- TODO: Figure out how to make the label the name of the region, instead of the code. -->
        <v-select
            ref="input"
            :input-id="fieldId"
            class="flex-1"
            append-to-body
            :calculate-position="positionOptions"
            :name="name"
            :clearable="true"
            :disabled="config.disabled || isReadOnly"
            :options="options"
            :searchable="true"
            :multiple="false"
            :reset-on-options-change="resetOnOptionsChange"
            :close-on-select="true"
            :value="value"
            :loading="loading"
            @input="vueSelectUpdated"
            @focus="$emit('focus')"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
            <template #no-options>
                <div class="text-sm text-gray-700 rtl:text-right ltr:text-left py-2 px-4" v-text="__('No options to choose from.')" />
            </template>
        </v-select>
    </div>
</template>

<script>
import PositionsSelectOptions from '../../../../vendor/statamic/cms/resources/js/mixins/PositionsSelectOptions';
import HasInputOptions from '../../../../vendor/statamic/cms/resources/js/components/fieldtypes/HasInputOptions.js';

export default {
    mixins: [Fieldtype, HasInputOptions, PositionsSelectOptions],

    inject: ['storeName'],

    data() {
        return {
            states: this.meta?.states,
            loading: false,
        }
    },

    computed: {
        country() {
            return this.$store.state.publish[this.storeName].values[this.config.from];
        },

        options() {
            return this.normalizeInputOptions(this.states?.map(state => ({ value: state.code, label: state.name })));
        },

        configParameter() {
            return utf8btoa(JSON.stringify(this.config));
        },

        resetOnOptionsChange() {
            // Reset the value if the value doesn't exist in the new set of options.
            return (options, old, val) => {
                let opts = options.map(o => o.value);
                return !val.some(v => opts.includes(v.value));
            };
        },
    },

    methods: {
        request(params = {}) {
            params = {
                config: this.configParameter,
                ...params,
            }

            return this.$axios.get(this.meta.url, { params }).then(response => {
                this.states = response.data.data;
                return Promise.resolve(response);
            });
        },

        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value)
            } else {
                this.update(null);
            }
        },
    },

    watch: {
        country (country) {
            this.loading = true;
            this.update(null);
            this.request({ country }).then(response => this.loading = false);
        },
    },
}
</script>