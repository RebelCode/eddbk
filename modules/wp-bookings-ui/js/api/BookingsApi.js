import { Api } from '@rebelcode/std-lib'

/**
 * API for interaction with bookings information on backend.
 *
 * @since [*next-version*]
 *
 * @class BookingsApi
 */
export default class BookingsApi extends Api {
  /**
   * Api constructor
   *
   * @since [*next-version*]
   *
   * @param {HttpClient} httpClient Http promise-based client
   * @param {Object<string, {method: String, endpoint: String}>} config
   * @param {RequestCache} cache Requests caching implementation.
   * @param {BookingReadTransformer} bookingReadTransformer Transformer for preparing booking for using in UI.
   */
  constructor (httpClient, config, cache, bookingReadTransformer) {
    super(httpClient, config, cache)
    this.bookingReadTransformer = bookingReadTransformer
  }

  /**
   * Fetch booking items using request params.
   *
   * @since [*next-version*]
   *
   * @param {Object} params Parameters that will be used for searching bookings.
   *
   * @return {Promise<any>}
   */
  fetch (params) {
    const fetchConfig = this.config['fetch']
    return this.http.request({
      method: fetchConfig.method,
      url: fetchConfig.endpoint,
      params
    })
  }

  /**
   * Create booking.
   *
   * @since [*next-version*]
   *
   * @param {Object} model Booking object for creating.
   *
   * @return {Promise<any>}
   */
  create (model) {
    const createConfig = this.config['create']
    return this.http.request({
      method: createConfig.method,
      url: createConfig.endpoint,
      data: this.prepareParams(model)
    })
  }

  /**
   * Update booking.
   *
   * @since [*next-version*]
   *
   * @param {{id: Number}} model Updated booking object.
   *
   * @return {Promise<any>}
   */
  update (model) {
    const updateConfig = this.config['update']
    return this.http.request({
      method: updateConfig.method,
      url: `${updateConfig.endpoint}${model.id}`,
      data: this.prepareParams(model)
    })
  }

  /**
   * Delete booking from server.
   *
   * @since [*next-version*]
   *
   * @param {{id: Number}} model Booking object that will be deleted.
   *
   * @return {Promise<any>}
   */
  delete (model) {
    const deleteConfig = this.config['delete']
    return this.http.request({
      method: deleteConfig.method,
      url: `${deleteConfig.endpoint}${model.id}`
    })
  }
}
