<template>
    <div>
        <div class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex justify-between">
                        <div class="flex items-start">
                            <SvgIcon name="dpd" class="h-10 w-10 mr-3" />

                            <div>
                                <div class="font-semibold text-md">DPD</div>
                                <span class="text-xs truncate text-gray-800 dark:text-dark-150">Amount: £5.64</span>
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