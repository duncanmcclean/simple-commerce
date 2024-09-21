<template>

    <coupon-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="title"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :breadcrumbs="breadcrumbs"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="listingUrl"
        @saved="saved"
    ></coupon-publish-form>

</template>

<script>
export default {

    props: [
        'title',
        'actions',
        'fieldset',
        'values',
        'meta',
        'breadcrumbs',
        'createAnotherUrl',
        'listingUrl'
    ],

    methods: {

        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        }

    }

}
</script>