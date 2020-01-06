/**
 * Create mixin that provides functionality to create datetimes.
 *
 * @since [*next-version*]
 *
 * @param {moment} moment Moment JS.
 *
 * @return {CreateDatetimeCapable}
 */
export default function MfCreateDatetimeCapable (moment) {
  return {
    inject: {
      /**
       * Function for creating moment instance in given timezone.
       *
       * @since [*next-version*]
       *
       * @property {CreateDatetimeFunction} createDatetime
       */
      createDatetime: {
        from: 'createDatetime',
        default () {
          return (value, timezone) => {
            return moment.tz(value, timezone || 'UTC')
          }
        }
      },

      /**
       * Function for creating moment object in local timezone.
       *
       * @since [*next-version*]
       *
       * @property {CreateLocalDatetimeFunction} createLocalDatetime
       */
      createLocalDatetime: {
        default () {
          return (value = null) => {
            if (!value) {
              value = moment()
            }
            return this.createDatetime(value, this.timezone)
          }
        }
      },

      /**
       * Function for creating the same time, but in local timezone.
       *
       * @since [*next-version*]
       *
       * @property {CreateSameLocalDatetimeFunction} createSameLocalDatetime
       */
      createSameLocalDatetime: {
        default () {
          return (value) => {
            const TZ_FREE_FORMAT = 'YYYY-MM-DD HH:ii:ss'
            const momentFixedTimezoneValue = moment.parseZone(value)
            return moment.tz(momentFixedTimezoneValue.format(TZ_FREE_FORMAT), TZ_FREE_FORMAT, this.timezone)
          }
        }
      },

      /**
       * Function for creating date strings.
       *
       * @since [*next-version*]
       *
       * @property {CreateDateStringFunction} createDateString
       */
      createDateString: {
        default () {
          return (value) => {
            return moment(value).format('YYYY-MM-DD')
          }
        }
      }
    }
  }
}