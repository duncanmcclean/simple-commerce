<template>
    <div class="flex">
        <v-select
            ref="input"
            :input-id="fieldId"
            class="flex-1"
            append-to-body
            searchable
            close-on-select
            clearable
            :calculate-position="positionOptions"
            :name="name"
            :disabled="config.disabled || isReadOnly || (multiple && limitReached)"
            :options="options"
            :multiple="multiple"
            :value="selectedOptions"
            :get-option-key="(option) => option.value"
            :loading="loading"
            @input="vueSelectUpdated"
            @focus="$emit('focus')"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
            <template #selected-option-container v-if="multiple"><i class="hidden"></i></template>
            <template #search="{ events, attributes }" v-if="multiple">
                <input
                    :placeholder="__(config.placeholder)"
                    class="vs__search"
                    type="search"
                    v-on="events"
                    v-bind="attributes"
                >
            </template>
            <template #option="{ label }">
                <div v-html="label" />
            </template>
            <template #selected-option="{ label }">
                <div v-html="label" />
            </template>
            <template #no-options>
                <div class="text-sm text-gray-700 rtl:text-right ltr:text-left py-2 px-4" v-text="__('No options to choose from.')" />
            </template>
            <template #footer="{ deselect }" v-if="multiple">
                <sortable-list
                    item-class="sortable-item"
                    handle-class="sortable-item"
                    :value="value"
                    :distance="5"
                    :mirror="false"
                    @input="update"
                >
                    <div class="vs__selected-options-outside flex flex-wrap">
                        <span v-for="option in selectedOptions" :key="option.value" class="vs__selected mt-2 sortable-item" :class="{'invalid': option.invalid}">
                            <div v-html="option.label" />
                            <button v-if="!readOnly" @click="deselect(option)" type="button" :aria-label="__('Deselect option')" class="vs__deselect">
                                <span>×</span>
                            </button>
                            <button v-else type="button" class="vs__deselect">
                                <span class="text-gray-500">×</span>
                            </button>
                        </span>
                    </div>
                </sortable-list>
            </template>
        </v-select>
        <div class="text-xs rtl:mr-2 ltr:ml-2 mt-3" :class="limitIndicatorColor" v-if="config.max_items > 1">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
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

        multiple() {
            return this.config.max_items !== 1;
        },

        selectedOptions() {
            let selections = this.value || [];

            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }

            return selections.map(value => {
                let option = this.options.find(option => option.value === value);

                if (! option) return {value, label: value};

                return {value: option.value, label: option.label, invalid: false};
            });
        },

        limitReached() {
            if (!this.config.max_items) return false;

            return this.currentLength >= this.config.max_items;
        },

        limitExceeded() {
            if (!this.config.max_items) return false;

            return this.currentLength > this.config.max_items;
        },

        currentLength() {
            if (this.value) {
                return (typeof this.value == 'string') ? 1 : this.value.length;
            }

            return 0;
        },

        limitIndicatorColor() {
            if (this.limitExceeded) {
                return 'text-red-500';
            } else if (this.limitReached) {
                return 'text-green-600';
            }

            return 'text-gray';
        },
    },

    methods: {
        vueSelectUpdated(value) {
            if (this.multiple) {
                this.update(value.map(v => v.value));
                // value.forEach((option) => this.states.push(option));
            } else {
                if (value) {
                    this.update(value.value)
                    // this.states.push(value)
                } else {
                    this.update(null);
                }
            }
        },

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
    },

    watch: {
        country (country) {
            this.loading = true;

            if (this.config.max_items === 1) {
                this.update(null);
            }

            this.request({ country }).then(response => this.loading = false);
        },
    },
}
</script>