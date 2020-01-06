/**
 * Toggleable component.
 *
 * @since [*next-version*]
 *
 * @param Vue
 * @return {*}
 * @constructor
 */
export function CfToggleable (Vue) {
    return Vue.extend({
        props: {
            /**
             * Toggleable model.
             *
             * @since [*next-version*]
             *
             * @param {FunctionalToggleable}
             */
            toggleable: {
                required: true
            }
        },

        computed: {
            /**
             * Is current model value equals true.
             *
             * @since [*next-version*]
             *
             * @return {boolean}
             */
            isOn () {
                return this.toggleable.isOn()
            }
        },

        methods: {
            /**
             * Set new state value.
             *
             * @since [*next-version*]
             *
             * @param {boolean} state New state for toggleable model.
             */
            setState (state) {
                this.toggleable.setState(state)
            }
        }
    })
}
