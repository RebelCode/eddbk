export default function (TabsParent) {
  return TabsParent.extend({
    props: {
      isTabsNavigationDisabled: {
        default: false
      },
      tabErrorClass: {
        default: 'horizontal-tabs__item--error'
      },
      tabsWithErrors: {}
    },
    methods: {
      /**
       * Get list of classes for the tab from object.
       *
       * @param {string|number} tabId Tab identifier.
       *
       * @return {string[]} The list of classes.
       */
      getTabClasses (tabId) {
        if (!this.tabsWithErrors || !this.tabsWithErrors[tabId]) {
          return []
        }
        return this.tabsWithErrors[tabId] ? [this.tabErrorClass] : []
      },

      /**
       * @inheritDoc
       */
      renderTabsSwitcher (h) {
        let tabs = this.getTabs()

        return h('div', {
          class: [
            this.isTabsNavigationDisabled ? 'disabled' : '',
            this.config.switcherClass
          ]
        }, tabs.map((tab, i) => {
          return h('div', {
            class: [
              this.config.switcherItemClass,
              this.value === this.getTabId(tab, i) ? this.config.switcherActiveItemClass : '',
              ...this.getTabClasses(this.getTabId(tab, i))
            ],
            on: {
              click: () => {
                this.$emit('input', this.getTabId(tab, i));
              }
            }
          }, tab.componentOptions.propsData.title)
        }))
      }
    }
  })
}
