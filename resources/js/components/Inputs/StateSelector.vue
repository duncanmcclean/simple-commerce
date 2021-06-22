<template>
    <div class="form-group w-full">
        <label class="block mb-1">State</label>
        <select v-model="state" class="input-text">
            <option
                v-for="state in states"
                :key="state.id"
                value="{{ $country['iso'] }}"
                v-text="state.name"
            ></option>
        </select>
    </div>
</template>

<script>
export default {
    props: ['value'],

    data() {
        return {
            busy: true,
            states: [],
            state: null,
        }
    },

    mounted() {
        // Watch country field
    },

    methods: {
        fetchStates() {
            // TODO
        },
    },

    watch: {
        state(value) {
            this.$emit('input', value)
        }
    },
}
</script>
