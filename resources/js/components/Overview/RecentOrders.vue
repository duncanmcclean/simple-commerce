<template>
  <div class="flex-1 card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2">
      <h2>
        <span>{{ __("Recent Orders") }}</span>
      </h2>
    </div>

    <ul class="px-2">
      <li
        v-for="order in data"
        :key="order.id"
        class="py-1 flex items-center justify-between"
      >
        <a :href="order.edit_url"
          ><strong>#{{ order.order_number }}</strong> -
          {{ order.grand_total }}</a
        >
        <span class="text-sm">{{ order.paid_date }}</span>
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
