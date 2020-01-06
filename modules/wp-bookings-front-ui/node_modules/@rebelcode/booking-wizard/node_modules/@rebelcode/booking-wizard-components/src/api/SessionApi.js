import { Api } from '@rebelcode/std-lib'

export default class SessionsApi extends Api {
  /**
   * Storing all sessions to work correctly with range caching system.
   *
   * @type {BookingStoredSession[]}
   */
  sessions = []

  /**
   * Api constructor
   *
   * @param {HttpClient} httpClient Http client like axios
   * @param {Object<string, {method: String, endpoint: String}>} config
   * @param {RequestCache} cache Requests caching implementation.
   * @param {RangeCache} rangeCache Range cache implementation.
   * @param {Transformer} sessionReadTransformer Session read transformer.
   * @param {Function} moment Moment JS.
   */
  constructor (httpClient, config, cache, rangeCache, sessionReadTransformer, moment) {
    super(httpClient, config, cache)

    this.rangeCache = rangeCache
    this.sessionReadTransformer = sessionReadTransformer
    this.moment = moment
  }

  /**
   * Fetch session list using search query
   *
   * @param params
   *
   * @return {*}
   */
  fetch (params) {
    const uncachedRange = this.rangeCache.getUncachedRange(params)
    if (!uncachedRange) {
      return Promise.resolve(this._getSessions(params))
    }

    const fetchConfig = this.config['fetch']
    return this.http.request({
      method: fetchConfig.method,
      url: fetchConfig.endpoint,
      params
    }).then(response => {
      this.rangeCache.remember(uncachedRange)
      const sessions = response.data.items.map(session => {
        return this.sessionReadTransformer.transform(session)
      })
      this._storeSessions(sessions)
      return this._getSessions(params)
    })
  }

  /**
   * Get sessions by given params.
   *
   * @param {number} service Service id to get sessions for
   * @param {string} start Start range in ISO8601 format
   * @param {string} end End range in ISO8601 format
   *
   * @return {BookingSession[]}
   */
  _getSessions({ service, start, end }) {
    start = this.moment(start).unix()
    end = this.moment(end).unix()
    const startNow = this.moment().unix()
    start = startNow > start ? startNow : start
    return this.sessions.filter(session => {
      return session.service === service
        && (session.startUnix < end || session.endUnix > start)
        && session.startUnix > start
    }).map(session => this._cleanSessionQueryFields(session))
  }

  /**
   * Store sessions in one place.
   *
   * @param {BookingSession[]} sessions
   */
  _storeSessions(sessions) {
    this.sessions = [...this.sessions, ...sessions.map(session => this._addSessionQueryFields(session))]
  }

  /**
   * Clear sessions and range cache.
   *
   * @since [*next-version*]
   */
  clearCache() {
    this.sessions = []
    this.rangeCache.clear()
  }

  /**
   * Add helping fields on session before saving it to store.
   *
   * @param {BookingSession} session Session to save in store.
   *
   * @return {BookingStoredSession} Session with added fields.
   */
  _addSessionQueryFields (session) {
    session = Object.assign({}, session)
    session['endUnix'] = this.moment(session.end).unix()
    return session
  }

  /**
   * Remove fields that were added for saving booking session in store.
   *
   * @param {BookingStoredSession} session Session from store.
   *
   * @return {BookingSession} Booking session without querying fields.
   */
  _cleanSessionQueryFields (session) {
    session = Object.assign({}, session)
    delete session['endUnix']
    return session
  }
}
