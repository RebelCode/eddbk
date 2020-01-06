import CheckboxClickAction from './CheckboxClickAction'
import AddBlockAction from './AddBlockAction'

/**
 * Available action factories definitions.
 *
 * @since [*next-version*]
 *
 * @return {Object.<string, UiAction>} List of UI action factories.
 */
export default function () {
  return {
    /**
     * Factory function for making CheckboxClickAction objects.
     *
     * @since [*next-version*]
     *
     * @param container
     *
     * @return {function(Object): CheckboxClickAction} Factory function for making CheckboxClickAction objects.
     */
    checkboxClickUiActionFactory (container) {
      return (args) => {
        return new CheckboxClickAction(container.jquery, args)
      }
    },

    /**
     * Factory function for making AddBlockAction objects.
     *
     * @since [*next-version*]
     *
     * @param container
     *
     * @return {function(Object): AddBlockAction} Factory function for making AddBlockAction objects.
     */
    addBlockUiActionFactory (container) {
      return (args) => {
        const renderBlockTemplate = container.makeTemplateRenderFunction(args.block)
        return new AddBlockAction(renderBlockTemplate, container.document, args)
      }
    }
  }
}