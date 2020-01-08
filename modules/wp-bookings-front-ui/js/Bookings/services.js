import Api from './Api'
import Store from './store'

/**
 * Creates module services definitions.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies List of dependencies.
 *
 * @return {object} List of module services definitions.
 */
export default function (dependencies) {
  return {
    /**
     * Wrapper for interacting with bookings API.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {CreateCapable}
     */
    'bookingsApi' (container) {
      return new Api(container.config.bookingsResourceUrl, container.authorizedHttpClient, container.config.initialBookingTransition)
    },

    /**
     * Bookings module store.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'bookingsStore' () {
      return Store
    },

    /**
     * Register store module key as a vuex module.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'vuexModules.booking' (container) {
      return 'bookingsStore'
    }
  }
}