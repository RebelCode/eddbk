import BookingStoreTransformer from './BookingStoreTransformer'
import AvailabilityStoreTransformer from './AvilabilityStoreTransformer'
import AvailabilityReadTransformer from './AvilabilityReadTransformer'
import ServiceReadTransformer from './ServiceReadTransformer'
import SessionTypeReadTransformer from './SessionTypeReadTransformer'
import BookingReadTransformer from './BookingReadTransformer'
import ServiceStoreTransformer from './ServiceStoreTransformer'
import ResourceReadTransformer from './ResourceReadTransformer'
import ResourceStoreTransformer from './ResourceStoreTransformer'

export default function (dependencies) {
  return {
    bookingStoreTransformer () {
      return new BookingStoreTransformer()
    },
    bookingReadTransformer () {
      return new BookingReadTransformer()
    },
    availabilityStoreTransformer (container) {
      return new AvailabilityStoreTransformer(container.moment, container.transformDatetimeForStore)
    },
    availabilityReadTransformer (container) {
      return new AvailabilityReadTransformer(container.moment, container.transformDatetimeForUi)
    },
    sessionTypeReadTransformer () {
      return new SessionTypeReadTransformer()
    },
    sessionReadTransformer (container) {
      return new dependencies.bookingWizardComponents.SessionReadTransformer(container.moment)
    },
    serviceReadTransformer (container) {
      return new ServiceReadTransformer(container.availabilityReadTransformer, container.sessionTypeReadTransformer)
    },
    serviceStoreTransformer (container) {
      return new ServiceStoreTransformer(container.availabilityStoreTransformer)
    },
    resourceStoreTransformer (container) {
      return new ResourceStoreTransformer(container.availabilityStoreTransformer)
    },
    resourceReadTransformer (container) {
      return new ResourceReadTransformer(container.availabilityReadTransformer)
    },
    transformDatetimeForUi (container) {
      /**
       * Transform datetime to work with it in UI
       *
       * @param {any} value
       * @param {string} timezone
       *
       * @returns {string}
       */
      return (value, timezone) => {
        return container.momentHelpers
          .switchToTimezone(value, timezone)
          .format(container.config.formats.datetime.tzFree)
      }
    },
    transformDatetimeForStore (container) {
      /**
       * Transform datetime to format required by server.
       *
       * @param {any} value
       * @param {string} timezone
       *
       * @returns {string}
       */
      return (value, timezone) => {
        return container.momentHelpers
          .createInTimezone(value, timezone)
          .format(container.config.formats.datetime.store)
      }
    }
  }
}