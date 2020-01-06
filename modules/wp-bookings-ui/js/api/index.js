import BookingsApi from './BookingsApi'
import ClientsApi from './ClientsApi'
import GeneralApiErrorHandler from './GeneralApiErrorHandler'
import SettingsApi from './SettingsApi'
import ServicesApi from './ServicesApi'
import StaffMembersApi from './StaffMembersApi'

/*
 * Exports instances to main container config.
 */
export default function (dependencies) {
  return {
    requestCache (container) {
      return new dependencies.stdLib.RequestCache(container.hashCode)
    },
    rangeCache (container) {
      return new dependencies.bookingWizardComponents.RangeCache(container.moment, dependencies.lodash.differenceWith, dependencies.lodash.isEqual)
    },
    /**
     * Instance of authorized HTTP client.
     *
     * @since [*next-version*]
     *
     * @return {HttpClient}
     */
    authorizedHttpClient (container) {
      return container.httpClient.create({
        headers: {
          'X-WP-Nonce': container.state.wp_rest_nonce
        }
      })
    },
    bookingsApi (container) {
      return new BookingsApi(container.authorizedHttpClient, container.state.endpointsConfig['bookings'], container.requestCache, container.bookingReadTransformer)
    },
    servicesApi (container) {
      return new ServicesApi(
        container.authorizedHttpClient,
        container.state.endpointsConfig['services'],
        container.requestCache,
        container.serviceReadTransformer,
        container.serviceStoreTransformer,
      )
    },
    staffMembersApi (container) {
      return new StaffMembersApi(
        container.authorizedHttpClient,
        container.state.endpointsConfig['staff_members'],
        container.requestCache,
        container.resourceReadTransformer,
        container.resourceStoreTransformer
      )
    },
    clientsApi (container) {
      return new ClientsApi(container.authorizedHttpClient, container.state.endpointsConfig['clients'], container.requestCache)
    },
    settingsApi (container) {
      let update = container.state.settingsUi ? container.state.settingsUi.updateEndpoint : {}
      if (!update) {
        update = {}
      }
      return new SettingsApi(container.httpClient, { update }, container.requestCache)
    },
    sessionsApi (container) {
      return new dependencies.bookingWizardComponents.SessionApi(
        container.authorizedHttpClient,
        container.state.endpointsConfig ? container.state.endpointsConfig['sessions'] : {},
        container.requestCache,
        container.rangeCache,
        container.sessionReadTransformer,
        container.moment
      )
    },
    apiErrorHandlerFactory (container) {
      return (handler) => {
        return new GeneralApiErrorHandler(handler)
      }
    }
  }
}