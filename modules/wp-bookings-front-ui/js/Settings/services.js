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
     * Settings module store.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object}
     */
    'settingsStore' () {
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
    'vuexModules.settings' (container) {
      return 'settingsStore'
    }
  }
}
