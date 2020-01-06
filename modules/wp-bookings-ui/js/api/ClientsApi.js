import { Api } from '@rebelcode/std-lib'

/**
 * API for interaction with clients information on backend.
 *
 * @since [*next-version*]
 *
 * @class ClientsApi
 */
export default class ClientsApi extends Api {
  /**
   * Fetch clients list using search parameters.
   *
   * @since [*next-version*]
   *
   * @param {Object} params Parameters that will be used for searching clients.
   *
   * @return {Promise<any>}
   */
  fetch (params) {
    const fetchConfig = this.config['fetch']
    return this.http.request({
      method: fetchConfig.method,
      url: fetchConfig.endpoint,
      data: this.prepareParams(params),
    })
  }

  /**
   * Create new client.
   *
   * @since [*next-version*]
   *
   * @param {Object} model Client object for creating.
   *
   * @return {Promise<any>}
   */
  create (model) {
    const createConfig = this.config['create']
    return this.http.request({
      method: createConfig.method,
      url: createConfig.endpoint,
      data: this.prepareParams(model),
    })
  }
}
