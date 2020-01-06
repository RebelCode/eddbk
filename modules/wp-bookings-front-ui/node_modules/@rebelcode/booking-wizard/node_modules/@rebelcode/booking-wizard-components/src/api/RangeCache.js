/**
 * Class for caching ranges.
 *
 * @since [*next-version*]
 */
export default class RangeCache {
  /**
   * Range cache underlying store.
   *
   * @since [*next-version*]
   *
   * @type {BookingSessionsRange[]}
   */
  rangeCache = []

  /**
   * Range cache constructor.
   *
   * @since [*next-version*]
   *
   * @param {moment} moment Moment JS.
   * @param {Function} differenceWith Function to finding difference between arrays using compare function.
   * @param {Function} isEqual Compare function that determines that objects are same.
   * @param {String} [period=month] Period of range cache
   */
  constructor (moment, differenceWith, isEqual, period = 'month') {
    this.moment = moment
    this.differenceWith = differenceWith
    this.isEqual = isEqual
    this.period = period
  }

  /**
   * Return the range which is difference between requested and cached
   * (the range that should be fetched in order to fill the gap)
   *
   * @since [*next-version*]
   *
   * @param {Number} service Service ID.
   * @param {String} start Range start datetime in ISO8601.
   * @param {String} end Range end datetime in ISO8601
   *
   * @return {BookingSessionsRange|null}
   */
  getUncachedRange ({service, start, end}) {
    // transform given range to the list of full months ranges
    const ranges = this._getFullPeriodRanges({service, start, end})
    // look which months from this list are not cached
    const uncached = this.differenceWith(ranges, this.rangeCache, this.isEqual)
    // to optimise the number of requests, we are merging uncached ranges by taking only first and last timestamps of the list
    if (uncached.length) {
      const first = uncached[0]
      const [last] = uncached.slice(-1)
      return {
        service,
        start: first.start,
        end: last.end
      }
    }
    return null
  }

  /**
   * Cache the query by remembering the list of full months of given range
   *
   * @since [*next-version*]
   *
   * @param {Number} service Service ID.
   * @param {String} start Range start datetime in ISO8601.
   * @param {String} end Range end datetime in ISO8601
   *
   * @return {BookingSessionsRange[]}
   */
  remember ({service, start, end}) {
    const ranges = this._getFullPeriodRanges({service, start, end})
    const uncached = this.differenceWith(ranges, this.rangeCache, this.isEqual)
    if (!uncached) {
      return
    }
    this.rangeCache = [...this.rangeCache, ...uncached]
    return this.rangeCache
  }

  /**
   * Clear range cache.
   *
   * @since [*next-version*]
   */
  clear () {
    this.rangeCache = []
  }

  /**
   * Get list of full period ranges.
   *
   * @since [*next-version*]
   *
   * @param {Number} service Service ID.
   * @param {String} start Range start datetime in ISO8601.
   * @param {String} end Range end datetime in ISO8601
   *
   * @return {BookingSessionsRange[]}
   */
  _getFullPeriodRanges ({service, start, end}) {
    const range = this.moment.range(this.moment(start).startOf(this.period), this.moment(end).endOf(this.period))
    const periods = Array.from(range.by(this.period)) // create an array from months iterator
    return periods.map(m => {
      return {
        service,
        start: m.startOf(this.period).format(),
        end: m.endOf(this.period).format()
      }
    })
  }
}
