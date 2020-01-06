/**
 * Request cache, allows to cache request results and use it without sending
 * new requests.
 *
 * @since [*next-version*]
 */
export class RequestCache {
  /**
   * Request cache constructor.
   *
   * @since [*next-version*]
   *
   * @param {Function} hashCode Function for getting hash code from object.
   */
  constructor (hashCode) {
    this.hashCode = hashCode
    this._cache = {}
  }

  /**
   * Remember result in cache. If it is already in cache - return it.
   *
   * @since [*next-version*]
   *
   * @param {object} params Params to retrieve from cache by.
   * @param {Function} request Request promise to remember result from.
   *
   * @return {Promise<any>}
   */
  remember (params, request) {
    if (this.has(params)) {
      return new Promise((resolve) => {
        resolve(this.get(params))
      })
    }
    return request().then(data => {
      this.set(params, data)
      return data
    })
  }

  /**
   * Store data in cache.
   *
   * @since [*next-version*]
   *
   * @param {object} params Params to retrieve from cache by.
   *
   * @param {*} data
   */
  set (params, data) {
    this._cache[this._getCacheKey(params)] = data
  }

  /**
   * Check cache has item.
   *
   * @since [*next-version*]
   *
   * @param {object} params Params to retrieve from cache by.
   *
   * @return {boolean}
   */
  has (params) {
    return !!this._cache[this._getCacheKey(params)]
  }

  /**
   * Get item from cache by params.
   *
   * @since [*next-version*]
   *
   * @param {object} params Params to retrieve from cache by.
   *
   * @return {*}
   */
  get (params) {
    return this._cache[this._getCacheKey(params)]
  }

  /**
   * Get cache key from params.
   *
   * @since [*next-version*]
   *
   * @param {*} params Key that should be casted to string.
   *
   * @return {string}
   */
  _getCacheKey (params) {
    let orderedParams = {}
    Object.keys(params).sort().map(key => {
      orderedParams[key] = params[key]
    })
    return this.hashCode(JSON.stringify(orderedParams))
  }
}
