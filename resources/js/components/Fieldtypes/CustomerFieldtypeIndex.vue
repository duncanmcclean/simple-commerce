<template>
    <div>
<!--        <div class="flex flex-wrap">-->
<!--            <div-->
<!--                v-for="(item, i) in items"-->
<!--                :key="item.id"-->
<!--                class="relationship-index-field-item h-5"-->
<!--                :class="{ 'mb-1.5': i < items.length-1 }"-->
<!--            >-->
<!--                <div class="flex items-center shrink">-->
<!--                    <div v-if="item.hasOwnProperty('published') && item.published !== null"-->
<!--                         class="little-dot h-1 w-1 rtl:ml-1 ltr:mr-1" :class="[item.published ? 'bg-green-600' : 'bg-gray-400 dark:bg-dark-200']" />-->
<!--                    <a :href="item.edit_url" :title="item.title" v-text="item.title" />-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <button v-if="hasMore && ! showingAll" @click.stop="showAll" class="mt-1 text-blue text-2xs" v-text="__('messages.view_more_count', {count: value.length - 2})" />-->
<!--        <button v-if="showingAll" @click.stop="hideExtra" class="mt-1 text-blue text-2xs" v-text="__('Hide')" />-->

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