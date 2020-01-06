/**
 * Abstract component for buttons group.
 *
 * It can be used as a base for different cases when
 * few buttons used as a same component (switcher, group).
 *
 * @constructor
 */
export default function CfAbstractButtonsGroup (Vue) {
  return Vue.extend({
    props: {
      /**
       * All buttons that should be rendered.
       *
       * In the simplest version this is object where keys is button id
       * and value is button's title. For better configurability it can be
       * an array with buttons objects inside.
       */
      buttons: {
        required: true
      }
    },

    methods: {
      /**
       * Button click event handler.
       *
       * Emmit event `button-clicked` with corresponding button identifier.
       * Button's identifier defined by consumer and it can be key in object that
       * represents buttons or button's property in array that represents buttons.
       *
       * @param buttonId
       */
      buttonClick (buttonId) {
        this.$emit('button-clicked', buttonId)
      }
    }
  })
}