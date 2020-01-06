/**
 * Tab component.
 *
 * @param Vue
 * @return {*}
 * @constructor
 */
export function CfTab(Vue) {
    return Vue.extend({
        props: {
            /**
             * Tab title in tabs switch.
             *
             * @type {String}
             */
            title: {
                type: String
            }
        },

        /**
         * Render function for component.
         *
         * @param {Function} h Vue's render function.
         * @return {VNode}
         */
        render (h) {
            return h('div', this.$slots.default);
        }
    });
}