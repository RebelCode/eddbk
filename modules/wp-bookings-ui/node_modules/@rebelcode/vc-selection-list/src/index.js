/**
 * Vue component that extend Repeater component and used for
 * creating components that can be used for selection item
 * (or items) from the passed collection.
 *
 * @since [*next-version*]
 *
 * @param {CfRepeater} Repeater Repeater instance.
 * @returns {VueComponent}
 * @constructor
 */
export function CfSelectionList (Repeater) {
    return Repeater.extend({
        props: {
            /**
             * v-model value.
             *
             * @since [*next-version*]
             *
             * @property {Array}
             */
            value: {},

            /**
             * Is current selection list multiple.
             *
             * @since [*next-version*]
             *
             * @property {Boolean}
             */
            multiple: {
                type: Boolean,
                default: false
            }
        },

        methods: {
            /**
             * Help repeater to emit v-model changing when using native selects.
             * We need to implement this because of Vue's render() limitation.
             *
             * This is necessary because when using a render() function, Vue
             * does not automatically handle input events.
             *
             * @see https://vuejs.org/v2/guide/render-function.html#v-model
             *
             * @since [*next-version*]
             *
             * @return {object}
             */
            getRepeaterRootOptions () {
                if (this.wrap === 'select') {
                    let self = this
                    return {
                        domProps: {
                            value: this.value,
                            multiple: this.multiple
                        },
                        on: {
                            input: function (event) {
                                self.$emit('input', self._getNativeSelectValue(event.target))
                            }
                        }
                    }
                }

                return {}
            },

            /**
             * Special repeater method, that enable
             * us to bypass child methods to repeating items.
             *
             * @since [*next-version*]
             *
             * @return {object} Methods available in repeating items
             */
            getRepeatedSlotProps () {
                return {
                    'isItemSelected': this.isItemSelected,
                    'select': this.select,
                    'getKey': this.getKey
                }
            },

            /**
             * Handle user's click on item
             *
             * @since [*next-version*]
             *
             * @param {object} item   Item from collection that user clicked on
             */
            select (item) {
                this.$emit('input', this._select(item, this.value))
            },

            /**
             * Check item is selected
             *
             * @since [*next-version*]
             *
             * @param {object} item Item from collection
             * @return {boolean}
             */
            isItemSelected (item) {
                return !!this._isItemSelected(item, this.value)
            },

            /**
             * Get item's key. This is required for using with native select
             * because we need to pass value to select's options.
             *
             * @since [*next-version*]
             *
             * @param {object} item Item from collection
             * @return {*}
             */
            getKey (item) {
                return this.collection._keyGetter(item)
            },

            /**
             * Check given item is selected
             *
             * @since [*next-version*]
             *
             * @param {object} item Item from collection that we are checking
             * @param {Array} currentValue Current value of selection list
             * @return {boolean} Is item selected
             *
             * @private
             */
            _isItemSelected (item, currentValue) {
                return currentValue.indexOf(this.getKey(item)) > -1
            },

            /**
             * Select item before emitting new model
             *
             * @since [*next-version*]
             *
             * @param {object} item Item from collection that is selected
             * @param {Array} currentValue Current value of selection list
             * @return {Array} New value for selection list component
             *
             * @private
             */
            _select (item, currentValue) {
                if (!this.multiple) {
                    return [this.getKey(item)]
                }

                var i = currentValue.indexOf(this.getKey(item))
                if (i === -1) {
                    currentValue.push(this.getKey(item))
                }
                else {
                    currentValue.splice(i, 1)
                }
                return currentValue
            },

            /**
             * Get select value, also can get multiple select value
             *
             * @since [*next-version*]
             *
             * @param el
             * @return {*}
             *
             * @private
             */
            _getNativeSelectValue (el) {
                if (!this.multiple) return [el.value]

                let result = []
                let options = el && el.options

                for (let i = 0; i < options.length; i++) {
                    if (options[i].selected) {
                        result.push(options[i].value || options[i].text);
                    }
                }

                return result
            }
        }
    })
}
