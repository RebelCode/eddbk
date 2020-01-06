/**
 * Switcher component.
 *
 * This is stacked buttons group that holds one state.
 * State is switched by clicking on some button by listening event
 * on self.
 *
 * @param AbstractButtonGroup
 * @constructor
 */
export default function CfSwitcher (AbstractButtonGroup) {
  return AbstractButtonGroup.extend({
    template: '#switcher-template',

    props: {
      /**
       * This is switcher's state, used as
       * v-model property on <switcher>
       */
      value: {},
    },

    mounted () {
      this.$on('button-clicked', this.buttonClicked)
    },

    methods: {
      /**
       * Some button in group is clicked.
       *
       * @param buttonId
       */
      buttonClicked (buttonId) {
        this.$emit('input', buttonId)
      }
    }
  })
}