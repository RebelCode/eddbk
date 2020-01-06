/**
 * Abstract dialog component, solid foundation for
 * any modals and dialogs that opened over the rest page content.
 *
 * @param Vue
 * @constructor
 */
export default function CfAbstractDialog (Vue) {
  return Vue.extend({
    props: {
      /**
       * Determines dialog visibility. This property is passed
       * from outside and cannot be changed inside dialog.
       * Dialog's consumer is responsible for manipulating dialog's visibility.
       *
       * @property {bool}
       */
      active: {
        type: Boolean
      },
    },

    watch: {
      /**
       * Watch for "active" property change and emit corresponding
       * event when it changed.
       *
       * @param isDialogActive {bool}
       */
      active (isDialogActive) {
        this.$emit(isDialogActive ? 'open' : 'close')
      }
    }
  })
}