import CheckboxClickAction from './CheckboxClickAction'
import AddBlockAction from './AddBlockAction'
import UiActionsPipe from './UiActionsPipe'

/**
 * Factory function for UI action pipes.
 *
 * @since [*next-version*]
 *
 * @param {Object.<string, *>} dependencies List of dependencies.
 * @param {Object} applicationState Application state.
 *
 * @return {Object.<string, UiActionsPipe>} All UI action pipes assembled from config.
 */
export default function (dependencies, applicationState) {
  const uiActions = applicationState ? applicationState.config['uiActions'] : null
  if (!uiActions) {
    return {}
  }
  let uiActionPipes = []
  for (const pipe of Object.keys(uiActions)) {
    const actionsConfig = uiActions[pipe]
    const pipeName = 'uiAction' + pipe.charAt(0).toUpperCase() + pipe.substr(1)
    uiActionPipes[pipeName] = container => {
      let actions = []
      for (const config of actionsConfig) {
        const makeAction = container[config.action + 'UiActionFactory']
        actions.push(makeAction(config.arguments))
      }
      return new UiActionsPipe(actions)
    }
  }
  return uiActionPipes
}