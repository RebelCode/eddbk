/**
 * Component for rendering items from the collection,
 * it iterate through all items and gives ability
 * to think only about rendered item template.
 *
 * @since [*next-version*]
 *
 * @param {Vue} Vue Vue instance.
 * @returns {Component}
 *
 * @constructor
 */
export function CfRepeater(Vue) {
    return Vue.extend({
        props: {
            /**
             * Collection for rendering items from.
             *
             * @since [*next-version*]
             *
             * @property {FunctionalCollection}
             */
            collection: {
                required: true
            },

            /**
             * Tag used for wrapping rendered result.
             *
             * @since [*next-version*]
             *
             * @property {string}
             */
            wrap: {
                type: String,
                default: 'div'
            }
        },

        computed: {
            /**
             * Get items from *injected* collection. Reactive.
             *
             * @since [*next-version*]
             *
             * @return {Array} Collection items as array
             */
            items () {
                return Object.values(this.collection.getItems())
            }
        },

        methods: {
            /**
             * Remove item from the collection.
             *
             * @since [*next-version*]
             *
             * @param {Object} item Some item from the collection.
             */
            removeItem (item) {
                this.collection.removeItem(item)
            },

            /**
             * Provide additional slot properties. Can be extended by child.
             *
             * @since [*next-version*]
             *
             * @return {Object}
             */
            getRepeatedSlotProps () {
                return {};
            },

            /**
             * Additional repeater root options. Can be extended by child.
             *
             * @since [*next-version*]
             *
             * @return {Object}
             */
            getRepeaterRootOptions () {
                return {};
            }
        },

        /**
         * Render component.
         *
         * @since [*next-version*]
         *
         * @param {Function} createElement Function for rendering.
         * @returns {VNode}
         */
        render (createElement) {
            return createElement(this.wrap, this.getRepeaterRootOptions(), this.items.map((item) => {
                let slotProps = Object.assign({
                    'remove': this.removeItem,
                    'item': item
                }, this.getRepeatedSlotProps());

                return this.$scopedSlots.default(slotProps)
            }))
        }
    })
}
