<template>
  <div class="flex-1 card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2">
      <h2>
        <span>{{ __("Top Customers") }}</span>
      </h2>
    </div>

    <ul class="px-2">
      <li
        v-for="customer in data"
        class="py-1 flex items-center justify-between"
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
