<template>
    <div>
        <div class="flex items-center">
            <div class="flex-1">
                <div class="truncate">
                    <a v-if="value.type === 'user' && value.viewable" :href="value.edit_url" target="_blank" class="truncate">
                        {{ value.name }}
                    </a>
                    <a v-else-if="value.type === 'guest'" class="truncate">
                        {{ value.name }}
                        <div class="status-index-field select-none status-draft ml-2">Guest</div>
                    </a>
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