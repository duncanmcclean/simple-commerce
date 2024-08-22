<template>
    <div>
        <div class="flex items-center">
            <div class="flex-1">
                <div class="truncate">
                    <a v-if="value.type === 'user' && value.viewable" :href="value.edit_url" class="truncate v-popper--has-tooltip">
                        {{ value.name }}
                    </a>
                    <div v-else-if="value.type === 'guest'" class="truncate v-popper--has-tooltip">
                        {{ value.name }}
                        <div class="status-index-field select-none status-draft ml-2">Guest</div>
                    </div>
                    <div v-else v-text="value.name" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [IndexFieldtype],
}
</script>