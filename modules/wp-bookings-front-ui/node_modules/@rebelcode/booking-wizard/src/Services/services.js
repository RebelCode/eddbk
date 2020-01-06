import Api from './Api'

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
     * Wrapper for interacting with services API.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {SelectCapable}
     */
    servicesApi (container) {
      return new Api(container.config.servicesResourceUrl, container.authorizedHttpClient)
    }
  }
}
