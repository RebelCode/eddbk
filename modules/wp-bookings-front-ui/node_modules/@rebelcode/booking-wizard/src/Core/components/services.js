import CfServiceStep from './CfServiceStep'
import CfConfirmationStep from './CfConfirmationStep'
import CfEddbkWizard from './CfEddbkWizard'
import CfSessionStep from './CfSessionStep'

/**
 * Components definitions.
 *
 * @since [*next-version*]
 *
 * @return {object} List of components.
 */
export default function (dependencies) {
  const appComponents = {
    /**
     * Main EDDBK wizard component.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'eddbk-wizard' (container) {
      return CfEddbkWizard(
        container.store,
        container.config.bookingDataMap,
        container.TranslateCapable,
        container.MapBookingFieldsCapable,
      )
    },

    /**
     * Component for selecting service in wizard.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'service-step' (container) {
      return CfServiceStep(
        container.VModelProxy,
        container.TranslateCapable,
        container.MapBookingFieldsCapable,
      )
    },

    /**
     * Component for selecting session in wizard.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'session-step' (container) {
      return CfSessionStep(
        container.VModelProxy,
        container.TranslateCapable,
        container.MapBookingFieldsCapable,
      )
    },

    /**
     * Confirmation step component in wizard.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'confirmation-step' (container) {
      return CfConfirmationStep(
        container.TranslateCapable,
        container.CreateDatetimeCapable,
        container.MapBookingFieldsCapable,
        container.config.datetime
      )
    },

    /**
     * Component for selecting session for service.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'service-session-selector' (container) {
      return dependencies.bookingWizardComponents.CfServiceSessionSelector(
        container.CreateDatetimeCapable,
        container.SessionsFilterCapable,
        container.sessionApi,
        container.config.datetime
      )
    },

    /**
     * Component for selecting date when selecting session for service.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'session-date-picker' (container) {
      return dependencies.bookingWizardComponents.CfSessionDatePicker(
        container.CreateDatetimeCapable,
        container.config.datetime
      )
    },

    /**
     * Component for selecting time when selecting session for service.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'session-time-picker' (container) {
      return dependencies.bookingWizardComponents.CfSessionTimePicker(
        container.CreateDatetimeCapable,
        container.config.datetime
      )
    },

    /**
     * Component for switching between near dates in calendar.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'date-navigator' (container) {
      return dependencies.bookingWizardComponents.CfDateNavigator(
        container.CreateDatetimeCapable,
        container.config.datetime
      )
    },

    /**
     * Component for selecting timezone.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'timezone-select' (container) {
      return dependencies.bookingWizardComponents.CfTimezoneSelect()
    },

    /**
     * Datepicker component.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    datepicker () {
      return dependencies.datepicker
    },

    /**
     * Form wizard component.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    'form-wizard' () {
      return dependencies.formWizard.FormWizard
    },

    /**
     * Wizard button component.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    'wizard-button' () {
      return dependencies.formWizard.WizardButton
    },

    /**
     * Wizard tab content component.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    'tab-content' () {
      return dependencies.formWizard.TabContent
    }
  }
  return {
    ...appComponents,

    /**
     * List of all registered components.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    components (container) {
      let components = {}
      /*
       * Get all registered components and provide them to
       * Vue root components only if their name is not started
       * with '_' or 'abstract'
       */
      Object.keys(appComponents).filter(key => {
        return key[0] !== '_' && key.indexOf('abstract') === -1
      }).map(key => {
        components[key] = container[key]
      })
      return components
    }
  }
}
