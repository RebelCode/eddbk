import VModelProxy from './VModelProxy'
import TranslateCapable from './TranslateCapable'
import MapBookingFieldsCapable from './MapBookingFieldsCapable'

/**
 * Creates mixins definitions.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies List of dependencies.
 *
 * @return {object} List of mixins definitions.
 */
export default function (dependencies) {
  return {
    /**
     * Mixin for proxying component's model value.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    VModelProxy () {
      return VModelProxy()
    },

    /**
     * Mixin that adds method for translating strings.
     *
     * @since [*next-version*]
     *
     * @return {object}
     */
    TranslateCapable (container) {
      return TranslateCapable(container.translate, container.getLabel)
    },

    /**
     * Mixin that adds computed properties of booking app store.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    MapBookingFieldsCapable (container) {
      return MapBookingFieldsCapable(container.mapStore, container.config.bookingDataMap)
    },

    /**
     * Mixin for providing datetime creation functions in components.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    CreateDatetimeCapable (container) {
      return dependencies.bookingWizardComponents.MfCreateDatetimeCapable(container.moment)
    },

    /**
     * Mixin for providing filterable feature in the session selector.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    SessionsFilterCapable (container) {
      return dependencies.bookingWizardComponents.MfSessionsFilterCapable(container.moment)
    }
  }
}
