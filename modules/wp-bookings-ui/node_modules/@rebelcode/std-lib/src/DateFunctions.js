/**
 * Create `createInTimezone` function.
 *
 * @param {moment} moment Moment JS object.
 *
 * @return {CreateInTimezoneFunction} `CreateInTimezone` function.
 */
export function makeCreateInTimezone (moment) {
  return (value, tz) => {
    const momentFixedTimezoneValue = moment.parseZone(value)
    if (tz.indexOf('UTC') !== 0) {
      return moment.tz(momentFixedTimezoneValue, tz)
    }
    let offset = Number(tz.replace(/UTC\+?/g, ''))
    return momentFixedTimezoneValue.utcOffset(offset)
  }
}

/**
 * Create `parseInTimezone` function.
 *
 * @param {moment} moment Moment JS object.
 * @param {string} timezoneFreeFormat Datetime format, free from timezone information.
 *
 * @return {ParseInTimezoneFunction} `parseInTimezone` function.
 */
export function makeParseInTimezone (moment, timezoneFreeFormat) {
  return (value, tz) => {
    const momentFixedTimezoneValue = moment.parseZone(value)
    if (tz.indexOf('UTC') !== 0) {
      return moment.tz(momentFixedTimezoneValue.format(timezoneFreeFormat), timezoneFreeFormat, tz)
    }
    let offset = Number(tz.replace(/UTC\+?/g, ''))
    return momentFixedTimezoneValue.utcOffset(offset, true)
  }
}
