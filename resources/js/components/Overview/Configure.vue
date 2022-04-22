<template>
  <div>
    <button class="btn flex items-center" @click="open = !open">
      Configure
      <svg viewBox="0 0 10 6.5" class="ml-1 w-2">
        <path
          fill="currentColor"
          d="M9.9 1.4 5 6.4l-5-5L1.4 0 5 3.5 8.5 0l1.4 1.4z"
        ></path>
      </svg>
    </button>

    <div
      class="popover-container dropdown-list"
      :class="{ 'popover-open': open }"
    >
      <div class="popover">
        <div
          class="popover-content bg-white shadow-popover rounded-md px-0 py-0"
        >
          <div class="outline-none text-left px-1 pt-2 pb-1">
            <h6 class="px-1 pb-1">Available Widgets</h6>

            <div
              v-for="widget in widgets"
              :key="widget.handle"
              class="column-picker-item"
            >
              <label class="cursor-pointer"
                ><input
                  type="checkbox"
                  v-model="selectedWidgets[`${widget.handle}`]"
                  @disabled="saving"
                  @change="setSharedStateColumns"
                  class="mr-1"
                />
                {{ widget.name }}
              </label>
            </div>
          </div>

          <div class="flex border-t text-grey-80">
            <button
              class="p-1 hover:bg-grey-10 hover:text-grey-80 rounded-bl text-xs flex-1 text-center"
              @click="reset"
              @disabled="saving"
            >
              Reset
            </button>
            <button
              class="p-1 hover:bg-grey-10 text-blue flex-1 rounded-br border-l text-xs text-center"
              @click="save"
              @disabled="saving"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HasPreferences from "../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences";

export default {
  props: {
    widgets: Array,
  },

  mixins: [HasPreferences],

  data() {
    return {
      open: false,
      saving: true,

      preferencesPrefix: "simple_commerce",

      selectedWidgets: {},
    };
  },

  mounted() {
    this.setInitialState();
  },

  methods: {
    setInitialState() {
      if (this.getPreference("overview_widgets")) {
        this.selectedWidgets = this.getPreference("overview_widgets");

        this.saving = false;

        return;
      }

      let selectedWidgets = {};

      this.widgets.forEach((widget) => {
        selectedWidgets[`${widget.handle}`] = true;
      });

      this.selectedWidgets = selectedWidgets;

      this.saving = false;
    },

    setSharedStateColumns() {
      this.$emit("selectedWidgets", this.selectedWidgets);
    },

    save() {
      this.saving = true;

      this.setPreference("overview_widgets", this.selectedWidgets);

      this.open = false;
    },

    reset() {
      this.saving = true;

      this.setInitialState();
    },
  },
};
</script>
