/**
 * Tabs component factory.
 *
 * @param Vue Configured Vue instance
 * @return {Tabs} Ready component.
 *
 * @since [*next-version*]
 */
export function CfTabs(Vue) {
    return Vue.extend({
        props: {
            /**
             * Active tab's id. Can be passed and
             * changed from outside. Handy for external controls or programmatic way
             * of changing active tab.
             *
             * @since [*next-version*]
             *
             * @type {String}
             */
            value: {},

            /**
             * Tabs options. Here is configuration
             * for tab classes etc.
             *
             * @since [*next-version*]
             *
             * @type {Object}
             */
            options: {
                type: Object,
                default () {
                    return {}
                }
            }
        },

        computed: {
            /**
             * Config for tabs holder and tabs. This config
             * includes classes that affect tab and tab-holder
             * appearance.
             *
             * @since [*next-version*]
             *
             * @return {Object}
             */
            config ()
            {
                let defaultConfig = {
                    switcherClass: 'tabs',
                    switcherItemClass: 'tabs-item',
                    switcherActiveItemClass: 'active',
                    tabsClass: 'tabs-content'
                };

                return {...defaultConfig, ...this.options};
            }
        },

        methods: {
            /**
             * Get all tabs
             *
             * @since [*next-version*]
             *
             * @return [VNode]
             */
            getTabs ()
            {
                return this.$slots.default.filter(component => {
                    return component.componentOptions && component.componentOptions.tag === 'tab'
                });
            },

            /**
             * Get tab ID.
             *
             * @since [*next-version*]
             *
             * @param {VNode} tab
             * @param {Number} i Tab index.
             * @return {Number | String}
             */
            getTabId (tab, i)
            {
                return tab.componentOptions.propsData.id || i;
            },

            /**
             * Render tab switcher
             *
             * @since [*next-version*]
             *
             * @param h
             * @return [VNode]
             */
            renderTabsSwitcher (h)
            {
                let tabs = this.getTabs();

                return h('div', {
                    class: this.config.switcherClass
                }, tabs.map((tab, i) => {
                    return h('div', {
                        class: [
                            this.config.switcherItemClass,
                            this.value === this.getTabId(tab, i) ? this.config.switcherActiveItemClass : ''
                        ],
                        on: {
                            click: () => {
                                this.$emit('input', this.getTabId(tab, i));
                            }
                        }
                    }, tab.componentOptions.propsData.title)
                }))
            },

            /**
             * Render active tab
             *
             * @since [*next-version*]
             *
             * @param h
             * @return [VNode]
             */
            renderTabs (h)
            {
                let tabs = this.getTabs();

                return h('div', {
                    class: this.config.tabsClass
                }, tabs.filter((tab, i) => {
                    return this.getTabId(tab, i) === this.value;
                }))
            }
        },

        /**
         * Component render function.
         *
         * @since [*next-version*]
         *
         * @param {Function} h Render function.
         * @return {VNode} Rendered component.
         */
        render (h) {
            return h('div', [
                this.renderTabsSwitcher(h),
                this.renderTabs(h)
            ])
        },
    });
}