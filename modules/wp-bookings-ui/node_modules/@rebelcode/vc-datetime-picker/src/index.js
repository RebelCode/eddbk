/**
 * Datetime picker constructor.
 * Can accept Date object as input and then utilize any
 * available library for date and time input.
 *
 * @since [*next-version*]
 *
 * @param Vue
 * @return {*}
 * @constructor
 */
export function CfDatetimePicker (Vue) {
    return Vue.extend({
        props: {
            /**
             * Timestamp in milliseconds, passed via v-model
             *
             * @since [*next-version*]
             *
             * @var {int}
             */
            value: {},

            /**
             * Locale to use with Date
             *
             * @since [*next-version*]
             *
             * @var {String}
             */
            locale: {
                type: String,
                default: 'en-US'
            }
        },

        computed: {
            /**
             * @since [*next-version*]
             *
             * It will force consumer to implement setter for timeValue
             * ONLY if consumer wants to use it.
             */
            timeValue: {
                get () {
                    return this.nativeDate.getUTCHours()
                        + ':' + this.nativeDate.getUTCMinutes()
                        + ':' + this.nativeDate.getUTCSeconds()
                },

                set (newValue) {
                    throw new Error('Implement computed timeValue setter in your extended datetime-picker.')
                }
            },

            /**
             * @since [*next-version*]
             *
             * It will force consumer to implement setter for dateValue
             * ONLY if consumer wants to use it.
             */
            dateValue: {
                get () {
                    return this.nativeDate.toISOString().substring(0, 10) // YYYY-MM-DD format
                },

                set (newValue) {
                    throw new Error('Implement computed dateValue setter in your extended datetime-picker.')
                }
            },

            /**
             * Wrapper for timestamp for simpler API
             *
             * @since [*next-version*]
             *
             * @return {Date}
             */
            nativeDate () {
                return new Date(this.value)
            }
        },

        methods: {
            /**
             * Set current component value (that binded to v-model).
             *
             * @since [*next-version*]
             *
             * @param {int} newValue  New value of this component.
             * @private
             */
            _setValue (newValue) {
                this.$emit('input', newValue)
            },

            /**
             * Get formatted datetime.
             *
             * @since [*next-version*]
             *
             * @param {object} options  Format options to format date string (using native toLocaleDateString).
             * @return {string}
             */
            format (options = {}) {
                return this.nativeDate.toLocaleDateString(this.locale, options)
            }
        }
    })
}
