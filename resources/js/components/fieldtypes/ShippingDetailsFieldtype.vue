<template>
    <div>
        <p v-if="! value.has_shipping_option" class="text-sm">{{ __('No shipping option was selected for this order.') }}</p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex items-center">
                        <SvgIcon v-if="value.shipping_method.logo" :name="value.shipping_method.logo" class="h-10 w-10 mr-3" />

                        <div class="flex flex-col space-y-1">
                            <span class="font-semibold text-md">{{ value.name }}</span>
                            <span class="text-xs">{{ value.shipping_method.name }}</span>
                        </div>
                    </div>

                    <div class="mt-5">
                        <ul class="list-none text-xs space-y-3">
                            <li v-for="(value, label) in value.details" :key="label">
                                <span class="font-semibold mr-1">{{ label }}:</span>
                                <span v-html="value"></span>
                            </li>
                        </ul>
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