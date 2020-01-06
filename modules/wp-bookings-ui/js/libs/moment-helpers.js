export function momentHelpers (moment, formats) {
  return {
    /**
     * Cache for weekdays in month. Example:
     *
     * {
     *  "2018/02.mon": [2, 9, 16, 23, 20]
     * }
     */
    weekdaysCache: {},

    /**
     * Create moment in given timezone but preserve local time. 'UTC+2...' format supported.
     *
     * This is useful for switching timezone, but holding time, for example you can switch from this:
     * - `2018-01-01T18:00:00+02:00 (UTC: 2018-01-01T16:00:00Z)`
     * to this:
     * - `2018-01-01T18:00:00+04:00 (UTC: 2018-01-01T14:00:00Z)`
     * by calling `createInTimezone('2018-01-01T18:00:00+02:00', 'UTC+4')`
     * 
     * @param {any} value Any value that moment can accept into constructor 
     * @param {string} tz String representing timezone, including UTC+${offset}
     *
     * @return {moment}
     */
    createInTimezone (value, tz) {
      const momentFixedTimezoneValue = moment.parseZone(value)
      if (tz.indexOf('UTC') !== 0) {
        return moment.tz(momentFixedTimezoneValue.format(formats.tzFree), formats.tzFree, tz)
      }
      let offset = Number(tz.replace(/UTC\+?/g, ''))
      return momentFixedTimezoneValue.utcOffset(offset, true)
    },

    /**
     * Switch timezone of given datetime.
     *
     * @param {any} value Any value that moment can accept into constructor
     * @param {string} tz String representing timezone, including UTC+${offset}
     *
     * @return {moment}
     */
    switchToTimezone (value, tz) {
      const momentFixedTimezoneValue = moment.parseZone(value)
      if (tz.indexOf('UTC') !== 0) {
        return moment.tz(momentFixedTimezoneValue, tz)
      }
      let offset = Number(tz.replace(/UTC\+?/g, ''))
      return momentFixedTimezoneValue.utcOffset(offset)
    },

    /**
     * Get label for timezone.
     * 
     * @param {string} tz String representing of timezone to display for humans in UI, including UTC+${offset}
     */
    timezoneLabel (tz) {
      return tz.replace(/_/g, ' ')
        .replace('.25', ':15')
        .replace('.5', ':30')
        .replace('.75', ':45')
    },

    /**
     * Check that dates are the same weekdays in months (two first mondays)
     *
     * @param {moment} firstDate
     * @param {moment} secondDate
     *
     * @return {boolean} Is dates are the same weekdays in months (two first mondays)
     */
    weekdayOfMonthIsSame (firstDate, secondDate) {
      if (firstDate.day() !== secondDate.day()) {
        return false
      }
      return this.weekdayInMonth(firstDate) === this.weekdayInMonth(secondDate)
    },

    /**
     * Get number of weekday
     *
     * @param date
     * @return {*}
     */
    weekdayInMonthNumber (date) {
      return {
        0: 'first',
        1: 'second',
        2: 'third',
        3: 'fourth',
        4: 'fifth',
      }[this.weekdayInMonth(date, 'number')]
    },

    /**
     * Get the weekday number in month. For example, 2 April 2018 is
     * *first* Monday of month and
     *
     * @param {moment} date Date to analyse
     * @param {bool|string} format Format to get, don't pass it if full number and week day needed (0mon|-1wed)
     */
    weekdayInMonth (date, format = false) {
      let weekdaysInMonth = this._getWeekdaysInMonth(date)
      let weekdayNumber = weekdaysInMonth.indexOf(date.date())

      let result = {
        number: weekdayNumber,
        day: date.format('ddd'),
      }

      if (format) {
        return result[format]
      }
      return result.number + result.day
    },

    /**
     * Get array of weekdays in month using cache.
     *
     * @param {moment} date Date to get same weekdays
     *
     * @return {array} Weekdays (Mondays) in give month: [2, 9, 16, 23, 20]
     */
    _getWeekdaysInMonth (date) {
      let key = date.format('YYYY/MM.ddd')

      if (!this.weekdaysCache[key]) {
        this.weekdaysCache[key] = this._createWeekdaysInMonth(date)
      }

      return this.weekdaysCache[key]
    },

    /**
     * Create weekdays for month
     *
     * @param {moment} date Date to get same weekdays
     */
    _createWeekdaysInMonth (date) {
      const firstWeekDay = moment(date)
        .startOf('month')
        .day(date.format('dddd'))

      let result = []

      if (firstWeekDay.date() > 7) {
        firstWeekDay.add(7, 'd')
      }

      var month = firstWeekDay.month()
      while (month === firstWeekDay.month()) {
        result.push(firstWeekDay.date())
        firstWeekDay.add(7, 'd')
      }

      return result
    }
  }
}