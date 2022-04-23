<template>
  <div>
    <header class="mb-3 flex justify-between items-center">
      <h1>{{ __("Overview") }}</h1>

      <overview-configure
        :widgets="widgets"
        @selectedWidgets="updateCurrentWidgets"
      />
    </header>

    <div
      v-if="showEntriesWarning"
      class="card bg-yellow py-2 px-4 leading-loose content mb-2 text-center"
    >
      <strong>Your store is growing!</strong> It has over 5k order entries.
      Consider switching to the
      <a
        class="text-blue"
        href="https://simple-commerce.duncanmcclean.com/database-orders"
        target="_blank"
        >Eloquent/Database driver</a
      >.
    </div>

    <div v-if="!data" class="card p-3 text-center">
      <loading-graphic />
    </div>

    <div v-if="data">
      <overview-orders-chart
        v-if="
          currentWidgets.map((widget) => widget.handle).includes('orders-chart')
        "
        :data="data['orders-chart']"
      />

      <div class="grid grid-cols-2 gap-2">
        <template v-for="currentWidget in currentWidgets">
          <component
            v-if="currentWidget.handle !== 'orders-chart'"
            :is="currentWidget.component"
            :data="data[currentWidget.handle]"
          ></component>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import HasPreferences from "../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences";

export default {
  props: {
    widgets: {
      type: Array,
      required: true,
    },
    showEntriesWarning: {
      type: Boolean,
      required: true,
    },
  },

  mixins: [HasPreferences],

  data() {
    return {
      preferencesPrefix: "simple_commerce",

      currentWidgets: null,

      data: null,
    };
  },

  mounted() {
    this.getCurrentWidgets();
  },

  methods: {
    getCurrentWidgets() {
      if (this.getPreference("overview_widgets")) {
        this.currentWidgets = Object.keys(
          this.getPreference("overview_widgets")
        )
          .filter((handle) => {
            return this.getPreference("overview_widgets")[handle] === true;
          })
          .map((handle) => {
            return this.widgets.find((widget) => widget.handle === handle);
          });

        this.refreshData();

        return;
      }

      this.currentWidgets = this.widgets;

      this.refreshData();
    },

    updateCurrentWidgets(currentWidgets) {
      this.currentWidgets = Object.keys(currentWidgets)
        .filter((handle) => currentWidgets[handle] === true)
        .map((handle) => {
          return this.widgets.find((widget) => widget.handle === handle);
        });

      this.refreshData();
    },

    refreshData() {
      axios
        .get(cp_url("/simple-commerce/overview"), {
          params: {
            widgets: this.currentWidgets.map((widget) => widget.handle),
          },
        })
        .then((response) => {
          this.data = response.data.data;
        });
    },
  },
};
</script>
