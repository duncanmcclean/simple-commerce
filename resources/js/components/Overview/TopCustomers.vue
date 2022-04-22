<template>
  <div class="flex-1 card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2">
      <h2>
        <span>{{ __("Top Customers") }}</span>
      </h2>
    </div>

    <ul>
      <li
        v-for="customer in data"
        :key="customer.id"
        class="px-2 py-1 flex items-center justify-between hover:bg-grey-10"
      >
        <a :href="customer.edit_url">{{ customer.email }}</a>
        <span class="text-sm">{{ customer.orders_count }} orders</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    data: Array,
  },
};
</script>
