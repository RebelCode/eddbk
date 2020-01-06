/**
 * Inline editor component.
 *
 * @param AbstractDialog
 *
 * @constructor
 */
export default function CfInlineEditor (AbstractDialog) {
  return AbstractDialog.extend({
    template: '#inline-editor-template',

    props: {
      /**
       * Modal title
       *
       * @property {string}
       */
      title: {
        type: String
      },
    },
  })
}