<template>
    <div>
        <div class="item">
            <div class="item-inner">
                <div class="w-full p-2">
                    <div class="flex justify-between">
                        <div class="flex items-start">
                            <SvgIcon name="mastercard" class="h-10 w-10 mr-3" />

                            <div>
                                <div class="font-semibold text-md">**** **** **** 4242</div>
                                <span class="text-xs truncate text-gray-800 dark:text-dark-150">Expires 09/2025</span>
                            </div>
                        </div>

                        <div class="flex items-center flex-1 justify-end">
                            <div class="text-4xs text-gray-600 uppercase whitespace-nowrap rtl:ml-2 ltr:mr-2 hidden @sm:block" v-text="`Stripe`" />

                            <div class="flex items-center">
                                <dropdown-list>
                                    <dropdown-item :text="__('Refund')" class="warning" />
                                </dropdown-list>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <ul class="list-none text-xs space-y-3">
                            <li><span class="font-semibold">Payment ID:</span> pi_1234567889abcefhkl</li>
                            <li><span class="font-semibold">Confirmed At:</span> 09/09/2024 12:46:12</li> <!-- TODO: Ensure this is output in the CP date/time format -->
                            <li><span class="font-semibold">Amount:</span> £200.99</li>
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