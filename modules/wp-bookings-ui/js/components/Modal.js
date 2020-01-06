/**
 * Modal component. Modal's consumer responsible for
 * providing any content to this modal, working with
 * date inside provided content, catching button clicks, etc.
 *
 * This is only UI box that can be opened and closed.
 *
 * @param AbstractDialog
 * @constructor
 */
export default function CfModal (AbstractDialog) {
  return AbstractDialog.extend({
    template: '#modal-template',

    inject: ['dom'],

    props: {
      /**
       * Modal title
       *
       * @property {string}
       */
      title: {
        type: String
      },

      /**
       * Additional class modifier for modal customization.
       *
       * @property {string}
       */
      modalBodyClass: {
        type: String,
        default: ''
      },


      /**
       * Class that applies to the body and used
       * to prevent body's scroll catch, so long dialog can be scrolled
       * without interfering with body scroll.
       *
       * @property {string}
       */
      dialogOpenedClass: {
        type: String,
        default: 'modal-opened'
      }
    },

    mounted () {
      /*
       * Add body "frozen" class to the body when dialog is opened.
       */
      this.$on('open', () => {
        this.dom.getElement('body')
          .classList
          .add(this.dialogOpenedClass);
      });

      /*
       * Remove body "frozen" class from the body when dialog is closed.
       */
      this.$on('close', () => {
        this.dom.getElement('body')
          .classList
          .remove(this.dialogOpenedClass);
      });
    },
  })
}