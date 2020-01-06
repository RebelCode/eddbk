/**
 * Timezone Select component.
 *
 * This component is wrapper for native select that allows usage
 * of backend's pre-enerated options for selection.
 *
 * @since [*next-version*]
 *
 * @constructor
 */
export default function CfTimezoneSelect () {
  return {
    template: '#timezone-select-template',

    props: {
      /**
       * This is select's state, used as
       * v-model property on <timezone-select>
       *
       * @since [*next-version*]
       */
      value: {}
    },

    computed: {
      /**
       * Computed value for inner component, wrapper for `v-model`
       *
       * @since [*next-version*]
       *
       * @property {any}
       */
      selectValue: {
        get () {
          return this.value
        },
        set (newValue) {
          this.$emit('input', newValue)
        }
      }
    }
  }
}
