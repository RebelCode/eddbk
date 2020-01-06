/**
 * Helper methods for availability, used across different components.
 *
 * @since [*next-version*]
 *
 * @class AvailabilityHelpers
 */
export default class AvailabilityHelpers {
  /**
   * AvailabilityHelpers constructor.
   *
   * @since [*next-version*]
   *
   * @param {Function} moment Moment JS.
   */
  constructor (moment) {
    this.moment = moment
  }

  /**
   * Check if availability is one day availability. So it can
   * be repeated weekly on some days.
   *
   * @since [*next-version*]
   *
   * @param {Availability} availability Availability for check.
   *
   * @return {boolean} Is availability is one day.
   */
  isOneDay (availability) {
    const nextDayStart = this.moment(availability.start).add(1, 'day').set({
      hour: 0,
      minute: 0,
      second: 0
    })
    const end = this.moment(availability.end)
    return end.isSameOrBefore(nextDayStart)
  }

  /**
   * Get full duration of availability in days.
   *
   * @since [*next-version*]
   *
   * @param {Availability} availability Availability for getting duration.
   *
   * @return {number} Full duration of availability in days.
   */
  getFullDuration (availability) {
    const startDate = this.moment(availability.start)
    startDate.set({
      hour: 0,
      minute: 0,
      second: 0
    })

    let endDate = this.moment(availability.end)
    if (availability.repeat) {
      if (availability.repeatUntil === 'period') {
        endDate = this.moment(startDate)
          .add(availability.repeatUntilPeriod, availability.repeatUnit)
      }
      else if (availability.repeatUntil === 'date') {
        endDate = this.moment(availability.repeatUntilDate)
      }
    }
    endDate.set({
      hour: 0,
      minute: 0,
      second: 0
    })

    return Math.abs(startDate.diff(endDate, 'days'))
  }
}