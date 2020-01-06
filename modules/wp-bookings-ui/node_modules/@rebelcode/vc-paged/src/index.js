/**
 * Vue component for rendering list of pages.
 * It know about active state of page, but doesn't make
 * any assumptions about meaning of "active"
 *
 * @since [*next-version*]
 *
 * @param Vue
 * @returns {VueComponent}
 */
export function CfPaged (Vue) {
    return Vue.extend({
        props: {
            /**
             * @since [*next-version*]
             *
             * @type {FunctionalCollection} Collection of pages.
             */
            items: {
                required: true
            },

            /**
             * @since [*next-version*]
             *
             * @type {FunctionalLimitedCollection} Collection of active pages keys.
             */
            selectedKeys: {
                required: true
            },

            /**
             * @since [*next-version*]
             *
             * @type {string} `Paged` wrapper.
             */
            wrapper: {
                default: 'div'
            },

            /**
             * As far in Vue we need to wrap component's template
             * in some root element, consumer of `Paged` can pass his
             * wrapper for pages.
             *
             * @since [*next-version*]
             *
             * @type {Function} Wrapper for the page root
             */
            pageWrapper: {
                default () {
                    /**
                     * @param {string} pageTemplate Page template.
                     * @param {Page} page Page that that will be wrapped. Doesn't used in default
                     *                    wrapper but may be used by consumer for applying some attributes
                     *                    depending on page activity state.
                     */
                    return (pageTemplate, page = null) => {
                        return '<div>' + pageTemplate + '</div>'
                    }
                }
            }
        },

        methods: {
            /**
             * Check whether a specific page is active.
             *
             * @since [*next-version*]
             *
             * @param {Page} item Some specific `Page` to be checked
             * @return {Boolean} Is page active
             */
            isPageActive (item) {
                return this.selectedKeys.hasItem(item.id)
            }
        },

        /**
         * Render template
         *
         * @since [*next-version*]
         *
         * @param {Function} h Function for rendering
         * @returns {VNode}
         */
        render (h) {
            let renderedPages = []
            let instance = this

            for (let page of this.items.getItems()) {
                let ctx = {
                    item: page,
                    items: instance.items,
                    isActive: instance.isPageActive(page)
                }

                let component = Vue.extend({
                    data () {
                        return ctx
                    },
                    template: this.pageWrapper(page.render(ctx), page)
                })

                renderedPages.push(h(component))
            }

            return h(this.wrapper, renderedPages)
        }
    })
}
