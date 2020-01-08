import humanizeDuration from './humanizeDuration'
import stringHelpers from './stringHelpers'
import makeMapStore from './makeMapStore'

/**
 * Exposing all utils of application.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies Dependencies list.
 *
 * @return {object}
 */
export default function (dependencies) {
  return {
    ...stringHelpers(dependencies),

    ...humanizeDuration(dependencies),

    /**
     * Function for mapping store fields.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {mapStoreFunction}
     */
    mapStore (container) {
      return makeMapStore(container.lodash.get)
    },

    /**
     * Function for creating datetimes in timezone.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {CreateDatetimeFunction}
     */
    createDatetime (container) {
      const createInTimezone = dependencies.stdLib.makeCreateInTimezone(container.moment)
      return (value, timezone) => {
        if (!timezone) {
          timezone = 'UTC'
        }
        return createInTimezone(value, timezone)
      }
    },

    /**
     * Function for creating datetime with the same time in local timezone.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {CreateSameLocalDatetimeFunction}
     */
    createSameLocalDatetime (container) {
      const createInTimezone = dependencies.stdLib.makeParseInTimezone(container.moment)
      return function (value) {
        return createInTimezone(value, this.timezone)
      }
    }
  }
}