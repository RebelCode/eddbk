/**
 * Transform service for reading with given options.
 *
 * @since [*nex-version*]
 *
 * @param item
 * @param api
 * @param transformOptions
 *
 * @return {*}
 */
const transformServiceForRead = (item, api, transformOptions) => {
  const options = Object.assign({}, transformOptions, {
    timezone: item.timezone
  })
  return api.serviceReadTransformer.transform(item, options)
}

/**
 * Last applied transform options for fetching.
 *
 * @since [*next-version*]
 *
 * @type {object|null}
 */
let lastTransformOptions = null

export default {
  /**
   * Fetch the list of services.
   *
   * @since [*next-version*]
   *
   * @param commit
   * @param api
   * @param params
   * @param transformOptions
   *
   * @return {PromiseLike<T> | Promise<T>}
   */
  fetch ({ commit }, { api, params, transformOptions }) {
    commit('setLoadingList', true)
    lastTransformOptions = transformOptions
    return api.fetch(params).then((response) => {
      commit('set', {
        key: 'services.list',
        value: response.data.items.map(item => {
          return transformServiceForRead(item, api, transformOptions)
        })
      }, {
        root: true
      })
      commit('setLoadingList', false)
    }).catch(() => {
      commit('setLoadingList', false)
    })
  },

  /**
   * Create the service.
   *
   * @since [*next-version*]
   *
   * @param {Function} commit Local's module commit Vuex method.
   * @param {ServicesApi} api The services API.
   * @param {{id: number,...}} model The model holding new values.
   *
   * @return {Promise<any>} Promise holding the server's response data.
   */
  create ({ commit }, { api, model }) {
    return api.create(model).then(({data}) => {
      return transformServiceForRead(data, api, lastTransformOptions)
    })
  },

  /**
   * Update the service.
   *
   * @since [*next-version*]
   *
   * @param {Function} commit Local's module commit Vuex method.
   * @param {ServicesApi} api The services API.
   * @param {{id: number, timezone: string, ...}} model The model holding new values.
   *
   * @return {Promise<any>} Promise holding the server's response data.
   */
  update ({ commit }, { api, model }) {
    return api.update(model).then(({data}) => {
      return transformServiceForRead(data, api, lastTransformOptions)
    })
  },

  /**
   * Seed service editor by passed model.
   *
   * @since [*next-version*]
   *
   * @param {ServicesApi} api The API for managing services.
   * @param {{id: number}} model
   */
  delete ({}, { api, model }) {
    return api.delete(model)
  },
}
