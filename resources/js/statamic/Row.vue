<template>
    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
        <!-- <td class="drag-handle" :class="sortableHandleClass"></td> -->
        <grid-cell
            v-for="(field, i) in fields"
            :show-inner="showField(field)"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :index="i"
            :row-index="index"
            :grid-name="name"
            :errors="errors(field.handle)"
            :error-key="errorKey(field.handle)"
            @updated="updated(field.handle, $event)"
            @meta-updated="metaUpdated(field.handle, $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <!-- <td class="row-controls">
            <dropdown-list>
                <dropdown-item v-if="canDelete" :text="__('Delete Row')" class="warning" @click="$emit('removed', index)" />
            </dropdown-list>
        </td> -->
    </tr>
</template>

<style scoped>
.draggable-mirror {
    display: none;
}
</style>

<script>
// import GridCell from '../../../vendor/statamic/cms/resources/js/components/fieldtypes/grid/Cell.vue';
import GridCell from './Cell.vue'
import { ValidatesFieldConditions } from '../../../vendor/statamic/cms/resources/js/components/field-conditions/FieldConditions.js'

export default {
    components: { GridCell },

    mixins: [ValidatesFieldConditions],

    props: {
        index: {
            type: Number,
            required: true,
        },
        fields: {
            type: Array,
            required: true,
        },
        values: {
            type: Object,
            required: true,
        },
        meta: {
            type: Object,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        errorKeyPrefix: {
            type: String,
        },
        canDelete: {
            type: Boolean,
            default: true,
        },
    },

    inject: [
        // 'grid',
        'sortableItemClass',
        'sortableHandleClass',
        'storeName',
    ],

    computed: {
        isExcessive() {
            // const max = this.grid.config.max_rows;
            const max = 10
            if (!max) return false
            return this.index >= max
        },
    },

    methods: {
        updated(handle, value) {
            this.values[handle] = value
            this.$emit('updated', handle, this.values)
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta)
            meta[handle] = value
            this.$emit('meta-updated', meta)
        },

        errorKey(handle) {
            return `${this.errorKeyPrefix}.${this.index}.${handle}`
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName]
            if (!state) return []
            return state.errors[this.errorKey(handle)] || []
        },
    },
}
</script>
