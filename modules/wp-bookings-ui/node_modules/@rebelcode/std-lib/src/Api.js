/**
 * Api abstraction to make interactions with backend using
 * some http client.
 *
 * @since [*next-version*]
 *
 * @class Api
 */
export class Api {
  /**
   * Api constructor
   *
   * @since [*next-version*]
   *
   * @param {HttpClient} httpClient Http promise-based client
   * @param {Object<string, {method: String, endpoint: String}>} config
   * @param {RequestCache} cache Requests caching implementation.
   */
  constructor (httpClient, config, cache) {
    this.http = httpClient
    this.config = config
    this.cache = cache
  }

  /**
   * Prepare params before submit.
   *
   * @since [*next-version*]
   *
   * @param {object} params Params that will be send to Api.
   *
   * @return {object} Prepared params.
   */
  prepareParams (params) {
    return params
  }
}