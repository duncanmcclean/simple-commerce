<template>
    <dropdown-list>
        <slot />
    </dropdown-list>
</template>

<script>
    import DropdownList from "../../../../vendor/statamic/cms/resources/js/components/DropdownList";

    export default {
        name: "ActionsList",

        components: {
            DropdownList,
        }
    }
</script>

<style scoped>

</style>
