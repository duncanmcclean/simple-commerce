<template>
    <div>
        <p v-if="! value.has_shipping_method" class="text-sm">No shipping method was chosen for this order.</p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex justify-between">
                        <div class="flex items-start">
                            <SvgIcon v-if="value.logo" :name="value.logo" class="h-10 w-10 mr-3" />

                            <div>
                                <div class="font-semibold text-md" v-text="value.name"></div>
                                <span class="text-xs truncate text-gray-800 dark:text-dark-150">Amount: {{ value.total }}</span>
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