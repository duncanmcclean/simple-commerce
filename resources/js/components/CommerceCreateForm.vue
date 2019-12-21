<template>
    <div>
        <slot />
    </div>
</template>

<script>
    export default {
        name: "CommerceCreateForm",

        methods: {
            redirect(response) {
                window.location = response.data.redirect;
            }
        }
    }
</script>

<style scoped>

</style>
