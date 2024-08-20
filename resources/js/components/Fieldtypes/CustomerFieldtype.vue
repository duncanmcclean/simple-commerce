<template>
    <div class="relationship-input-items space-y-1 outline-none">
        <div class="item select-none item outline-none">
            <div class="item-inner">
                <div
                    v-if="value.invalid"
                    v-tooltip.top="__('An item with this ID could not be found')"
                    v-text="value.id" />

                <a v-if="value.type === 'user' && value.viewable" :href="value.edit_url" @click.prevent="edit" class="truncate v-popper--has-tooltip">
                    {{ value.name }}
                </a>

                <a v-else-if="value.type === 'guest' && value.viewable" @click.prevent="edit" class="truncate v-popper--has-tooltip">
                    {{ value.name }}
                    <div class="status-index-field select-none status-draft ml-1">Guest</div>
                </a>

                <div v-else v-text="value.name" />

                <inline-edit-form
                    v-if="isEditingUser"
                    :item="value"
                    :component="meta.user.formComponent"
                    :component-props="meta.user.formComponentProps"
                    @updated="itemUpdated"
                    @closed="isEditingUser = false"
                />

                <div class="flex items-center flex-1 justify-end">
                    <div class="flex items-center">
                        <dropdown-list>
                            <dropdown-item v-if="value.editable" :text="__('Edit')" @click="edit" />
                            <dropdown-item v-else-if="value.viewable" :text="__('View')" @click="edit" />
                        </dropdown-list>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import InlineEditForm from '../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/InlineEditForm.vue'

export default {
    components: {
        InlineEditForm
    },

    mixins: [Fieldtype],

    data() {
        return {
            isEditingUser: false,
        }
    },

    methods: {
        edit() {
            if (! this.value.editable) return;
            if (this.value.invalid) return;

            if (this.value.reference && Object.entries(this.$store.state.publish).find(([key, value]) => value.reference === this.value.reference)) {
                this.$toast.error(__("You're already editing this item."));
                return;
            }

            if (this.value.type === 'user') {
                this.isEditingUser = true;
                return;
            }
        },

        itemUpdated(responseData) {
            this.$emit('updated', {
                ...this.value,
                // in case we need to merge anything in here
            })
        },
    }
}
</script>