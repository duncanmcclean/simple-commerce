<template>
    <div class="receipt-line-item">
        <div>
            <div v-if="lineItem.product.invalid" v-tooltip.top="__('A product with this ID could not be found')" v-text="lineItem.product.title" />

            <a v-else @click.prevent="edit" :href="lineItem.product.edit_url">
                {{ lineItem.product.title }}
                <span v-if="lineItem.variant" class="text-sm" v-text="`(${lineItem.variant.name})`"></span>
            </a>
        </div>
        <div>{{ lineItem.unit_price }}</div>
        <div>{{ lineItem.quantity }}</div>
        <div>{{ lineItem.sub_total }}</div>

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