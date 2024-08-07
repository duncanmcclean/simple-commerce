<template>
    <div class="receipt-line-item">
        <div>
            <!-- TODO: Support for product variants -->
            <template v-if="!lineItem.product.invalid">
                <a @click.prevent="edit" :href="lineItem.product.edit_url">
                    {{ lineItem.product.title }}
                </a>
            </template>
        </div>
        <div>{{ lineItem.unit_price }}</div>
        <div>{{ lineItem.quantity }}</div>
        <div>{{ lineItem.total }}</div>

        <inline-edit-form
            v-if="isEditing"
            :item="lineItem.product"
            :component="formComponent"
            :component-props="formComponentProps"
            @updated="itemUpdated"
            @closed="isEditing = false"
        />
    </div>
</template>

<script>
import InlineEditForm from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/InlineEditForm.vue'

export default {
    components: {
        InlineEditForm
    },

    props: {
        lineItem: Object,
        formComponent: String,
        formComponentProps: Object,
    },

    data() {
        return {
            isEditing: false,
        }
    },

    methods: {
        edit() {
            // if (! this.editable) return;
            // if (this.item.invalid) return;

            if (this.lineItem.product.reference && Object.entries(this.$store.state.publish).find(([key, value]) => value.reference === this.lineItem.product.reference)) {
                this.$toast.error(__("You're already editing this item."));
                return;
            }

            this.isEditing = true;
        },

        itemUpdated(responseData) {
            this.$emit('updated', {
                ...this.lineItem,
                product: {
                    ...this.lineItem.product,
                    title: responseData.title,
                    published: responseData.published,
                    private: responseData.private,
                    status: responseData.status,
                }
            })
        },
    }
}
</script>