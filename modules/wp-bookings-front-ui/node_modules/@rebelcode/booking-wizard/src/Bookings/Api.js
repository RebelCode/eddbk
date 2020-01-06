/**
 * Bookings API.
 *
 * @since [*next-version*]
 *
 * @implements {CreateCapable}
 */
export default class Api {
  /**
   * BookingsApi constructor.
   *
   * @since [*next-version*]
   *
   * @param {string} resourceUrl URL of bookings resource.
   * @param {HttpClient} httpClient Promise-based http client.
   * @param {string} initialTransition Name of initial transition for bookings.
   */
  constructor (resourceUrl, httpClient, initialTransition) {
    this.resourceUrl = resourceUrl
    this.httpClient = httpClient
    this.initialTransition = initialTransition
  }

  /**
   * Create booking.
   *
   * @since [*next-version*]
   *
   * @param {Booking} data Booking object to create.
   *
   * @return {Promise<any>} Booking creation promise.
   */
  create (data) {
    return this.httpClient.request({
      url: this.resourceUrl,
      method: 'post',
      data
    })
  }
}