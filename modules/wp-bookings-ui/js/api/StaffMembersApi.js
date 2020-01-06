import { Api } from '@rebelcode/std-lib'

/**
 * API for interaction with the staff members backend.
 *
 * @since [*next-version*]
 *
 * @class StaffMembersApi
 */
export default class StaffMembersApi extends Api {
  /**
   * Api constructor
   *
   * @since [*next-version*]
   *
   * @param {HttpClient} httpClient Http promise-based client
   * @param {Object<string, {method: String, endpoint: String}>} config
   * @param {RequestCache} cache Requests caching implementation.
   * @param {ResourceReadTransformer} resourceReadTransformer Transformer applied to a resource entity before displaying.
   * @param {ResourceReadTransformer} resourceReadTransformer Transformer applied to a resource entity before displaying.
   * @param {ResourceStoreTransformer} resourceStoreTransformer Transformer applied to a resource entity before saving.
   */
  constructor (httpClient, config, cache, resourceReadTransformer, resourceStoreTransformer) {
    super(httpClient, config, cache)
    this.resourceReadTransformer = resourceReadTransformer
    this.resourceStoreTransformer = resourceStoreTransformer
  }

  /**
   * Fetch the list using request params.
   *
   * @since [*next-version*]
   *
   * @param {Object} params Parameters that will be used for searching staff members.
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
   * Create a staff member.
   *
   * @since [*next-version*]
   *
   * @param {Object} model Staff member object for creating.
   *
   * @return {Promise<any>}
   */
  create (model) {
    model = this.resourceStoreTransformer.transform(JSON.parse(JSON.stringify(model)), {timezone: model.availability.timezone})
    const createConfig = this.config['create']
    return this.http.request({
      method: createConfig.method,
      url: createConfig.endpoint,
      data: this.prepareParams(model)
    })
  }

  /**
   * Update the staff member.
   *
   * @since [*next-version*]
   *
   * @param {{id: Number}} model Updated the staff member object.
   *
   * @return {Promise<any>}
   */
  update (model) {
    model = this.resourceStoreTransformer.transform(JSON.parse(JSON.stringify(model)), {timezone: model.availability.timezone})
    const updateConfig = this.config['update']
    return this.http.request({
      method: updateConfig.method,
      url: `${updateConfig.endpoint}${model.id}`,
      data: this.prepareParams(model)
    })
  }

  /**
   * Delete a staff member from a server.
   *
   * @since [*next-version*]
   *
   * @param {{id: Number}} model Staff member object that will be deleted.
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
