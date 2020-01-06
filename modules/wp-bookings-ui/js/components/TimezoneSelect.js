/**
 * Timezone Select component.
 *
 * This component is wrapper for native select that allows usage
 * of backend's pre-enerated options for selection.
 *
 * @constructor
 */
export default function CfTimezoneSelect() {
  return {
    template: '#timezone-select-template',

    props: {
      /**
       * This is select's state, used as
       * v-model property on <timezone-select>
       */
      value: {}
    },

    computed: {
      /**
       * Computed value for inner component, wrapper for `v-model` 
       * 
       * @property {any}
       */
      selectValue: {
        get() {
          return this.value
        },
        set(newValue) {
          this.$emit('input', newValue)
        }
      }
    }
  }
}
