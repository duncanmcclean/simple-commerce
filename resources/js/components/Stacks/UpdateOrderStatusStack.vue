<template>
    <div>
        <stack
                name="createOrderStatus"
                @closed="close"
        >
            <div class="h-full bg-white overflow-auto">
                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between mb-4">
                    Simple Commerce Settings
                    <button type="button" class="btn-close" @click="close">×</button>
                </div>

                <div class="px-4">
                    <publish-form
                        id="update-order-status-form"
                        name="update-order-status-form"
                        title="Update Order Status"
                        :action="action"
                        :blueprint="blueprint"
                        :meta="meta"
                        :values="values"
                        @saved="saved"
                    ></publish-form>
                </div>
            </div>
        </stack>
    </div>
</template>

<script>
    export default {
        name: "UpdateOrderStatusStack",

        props: [
            'action', 'blueprint', 'meta', 'values'
        ],

        methods: {
            close() {
                this.$emit('closed', true);
            },

            saved() {
                this.$emit('saved', true);
            }
        }
    }
</script>

<style scoped>

</style>
