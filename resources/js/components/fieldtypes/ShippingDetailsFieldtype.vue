<template>
    <div>
        <p v-if="! value.has_shipping_option" class="text-sm">No shipping option was selected for this order.</p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex justify-between">
                        <div class="flex items-start">
                            <SvgIcon v-if="value.shipping_method.logo" :name="value.shipping_method.logo" class="h-10 w-10 mr-3" />

                            <div class="flex flex-col space-y-1">
                                <div class="font-semibold text-md" v-text="value.name"></div>
                                <span class="block text-xs truncate text-gray-800 dark:text-dark-150">Amount: {{ value.total }}</span>
                                <span v-if="value.tracking_number" class="block text-xs truncate text-gray-800 dark:text-dark-150">Tracking Number: {{ value.tracking_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SvgIcon from '../SvgIcon.vue'

export default {
    components: { SvgIcon },

    mixins: [Fieldtype],
}
</script>