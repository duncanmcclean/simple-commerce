<template>
    <div>
        <p v-if="! value.has_payment_gateway" class="text-sm">{{ __('No payment was required for this order.') }}</p>

        <div v-else class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex items-center">
                        <SvgIcon v-if="value.logo" :name="value.logo" class="h-10 w-10 mr-3" />

                        <div class="font-semibold text-md">{{ value.title }}</div>
                    </div>

                    <div v-if="value.details" :class="{ 'mt-3': !value.logo, 'mt-5': value.logo }">
                        <ul class="list-none text-xs space-y-2">
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