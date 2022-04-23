<template>
  <div class="flex-1 card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2 pb-1">
      <h2>
        <span>{{ __("Low Stock Products") }}</span>
      </h2>
    </div>

    <ul v-if="data.length">
      <li
        v-for="product in data"
        :key="product.id"
        class="px-2 py-1 flex items-center justify-between hover:bg-grey-10"
      >
        <a :href="product.edit_url">{{ product.title }}</a>
        <span class="text-sm" :class="{ 'text-red-light': product.stock === 0 }"
          >{{ product.stock }} remaining</span
        >
      </li>
    </ul>

    <p v-else class="px-2 pt-1 pb-2" style="color: #737f8c">No results</p>
  </div>
</template>

<script>
export default {
  props: {
    data: Array,
  },
};
</script>
