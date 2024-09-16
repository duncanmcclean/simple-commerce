<template>
    <div class="status-index-field select-none" :class="statusClass">
        {{ value.label }}
    </div>
</template>

<script>
export default {
    mixins: [IndexFieldtype],

    computed: {
        statusClass() {
            switch (this.value.value) {
                case 'payment_pending':
                    return 'bg-gray-300 text-gray-900 dark:text-gray-950 bg-gray-200 dark:bg-gray-300';
                case 'cancelled':
                    return 'bg-red-500 text-red-50 dark:text-red-950 bg-red-200 dark:bg-red-300';
                default:
                    return 'bg-green-500 text-green-900 dark:text-green-950 bg-green-200 dark:bg-green-300';
            }
        },
    }
}
</script>