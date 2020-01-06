/**
 * Component for selecting date from month for selecting sessions from.
 *
 * @since [*next-version*]
 *
 * @param {CreateDatetimeCapable} CreateDatetimeCapable Mixin that provides ability to work with datetime.
 * @param {{
 *  sessionTime: (string), // How to format session time,
 *  dayFull: (string), // How to format day for day heading,
 *  dayShort: (string), // How to format day for sessions,
 * }} dateFormats Map of date formats for formatting dates in UI.
 *
 * @return {object}
 */
export default function (CreateDatetimeCapable, dateFormats) {
  return {
    template: '#session-date-picker-template',

    mixins: [ CreateDatetimeCapable ],

    inject: {
      /**
       * Datepicker component, used for selecting month and day.
       *
       * @since [*next-version*]
       */
      'datepicker': 'datepicker',
    },

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {string|null} timezone Name of timezone in which sessions will be displayed.
       */
      timezone: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {String|null} selectedDay Selected day to show sessions from.
       */
      selectedDay: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {BookingSession|null} session Selected session, sync with parent
       */
      session: {
        default: null
      },

      /**
       * The previous closest available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null}
       */
      prevAvailableDay: {
        default: null
      },

      /**
       * The next closest available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null}
       */
      nextAvailableDay: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {boolean} isDailyDuration Is selected session duration is longer than 1 day.
       */
      isDailyDuration: {
        default: false
      },

      /**
       * @since [*next-version*]
       *
       * @property {Boolean} loading Is sessions information for month are loading right now.
       */
      loading: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {object[]} sessions List of all sessions available for current month.
       */
      availableSessions: {
        default () {
          return []
        }
      },

      /**
       * @since [*next-version*]
       *
       * @property {Date} openedOnDate Date, on which datepicker is opened.
       */
      openedOnDate: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {BookingSession[]} value Session list for selected day, `v-model` in parent
       */
      value: {},
    },

    computed: {
      /**
       * Proxy for component's model field.
       *
       * @since [*next-version*]
       *
       * @property {object|null} value Selected day, `v-model` in parent
       */
      selectedDayProxy: {
        get () {
          if (!this.selectedDay) {
            return null
          }

          /*
           * Timestamp is added due to missing default time in `Date` constructor.
           *
           * Assuming that timezone is LA:
           *
           * `new Date('2018-09-12') -> Tue Sep 11 2018 18:00:00 GMT-0600`
           * `new Date('2018-09-12 00:00:00') -> Wed Sep 12 2018 00:00:00 GMT-0600` - in Chrome, in Safari this doesn't work.
           *
           * Solution that works in all browsers:
           * `new Date(2018, 8, 12) -> Wed Sep 12 2018 00:00:00 GMT-0600` - 8 is September index (9), count goes from 0
           */
          const date = this.selectedDay.split('-')
          return new Date(
            Number(date[0]),
            Number(date[1]) - 1,
            Number(date[2]),
          )
        },
        set (value) {
          this.$emit('update:selectedDay', this.createDateString(value))
        }
      },

      /**
       * Computed property that maps days (string) to sessions (array of sessions).
       *
       * @since [*next-version*]
       *
       * @property {object} Map of dates and corresponding sessions for each of them.
       */
      daysWithSessions () {
        let daysWithSessions = {}

        for (let session of this.availableSessions) {
          const sessionDays = this._getSessionDays(session)

          for (let sessionDay of sessionDays) {
            let sessionDayKey = this._getDayKey(sessionDay)
            if (!daysWithSessions[sessionDayKey]) {
              daysWithSessions[sessionDayKey] = []
            }

            if (this.isDailyDuration) {
              daysWithSessions[sessionDayKey] = [session]
            }
            else {
              daysWithSessions[sessionDayKey].push(session)
            }
          }
        }

        return daysWithSessions
      },

      /**
       * List of available days with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string[]}
       */
      availableDays () {
        return Object.keys(this.daysWithSessions)
      },

      /**
       * The date object, that represents today.
       *
       * @since [*next-version*]
       *
       * @property {Date}
       */
      today () {
        const startOfToday = this.createLocalDatetime().startOf('day')
        return new Date(startOfToday.year(), startOfToday.month(), startOfToday.date())
      },

      /**
       * Days of selected session.
       *
       * @since [*next-version*]
       *
       * @property {Date[]}
       */
      sessionDays () {
        if (!this.isDailyDuration || !this.session) {
          return []
        }
        return this._getSessionDays(this.session)
      },
    },

    watch: {
      /**
       * Update sync properties once selected day is changed.
       *
       * @since [*next-version*]
       *
       * @param {string} value Selected date.
       */
      selectedDay (value) {
        const selectedDaySessions = this.getSessionsForDay(value)

        this.$emit('input', selectedDaySessions)
        this.$emit('update:nextAvailableDay', this.getNextAvailableDay(value))
        this.$emit('update:prevAvailableDay', this.getPrevAvailableDay(value))
      },

      /**
       * Watch for timezone change, and deselect session and date, if
       * there are no sessions available on that date.
       *
       * @since [*next-version*]
       */
      timezone () {
        if (this.loading) {
          return
        }

        const selectedDay = this.selectedDay

        this.$emit('update:session', null)
        this.$emit('update:selectedDay', null)

        // select day again, if there are some sessions on that date.
        if (this.daysWithSessions[selectedDay] && this.daysWithSessions[selectedDay].length) {
          this.$nextTick(() => {
            this.$emit('update:selectedDay', selectedDay)
          })
        }
      },

      /**
       * If availableSessions is just loaded but day is already selected,
       * emit selection day event again to fulfill data.
       *
       * @since [*next-version*]
       */
      availableSessions: {
        deep: true,
        handler (newValue, oldValue) {
          if (oldValue.length === 0 && this.selectedDay) {
            this.$emit('update:selectedDay', new Date(this.selectedDay))
          }
        }
      },
    },

    methods: {
      /**
       * Event listener, fired on month change.
       *
       * @since [*next-version*]
       *
       * @param {Date} newMonth Newly selected month.
       */
      onMonthChange (newMonth) {
        this.$emit('update:openedOnDate', newMonth)
        this.$emit('changedMonth', newMonth)
      },

      /**
       * Sessions list for selected day.
       *
       * @since [*next-version*]
       *
       * @param {string} day Day to get sessions for.
       *
       * @return {object[]}
       */
      getSessionsForDay (day) {
        let selectedDaySessions = []
        if (day) {
          selectedDaySessions = this.daysWithSessions[day] || []
        }
        return selectedDaySessions
      },

      /**
       * Get the closest next available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null} day Day for which get sessions.
       *
       * @return {string} The closest next available day with sessions.
       */
      getNextAvailableDay (day) {
        const selectedDayIndex = this.availableDays.indexOf(day)
        const availableDaysCount = this.availableDays.length
        let result = null
        if (availableDaysCount - 1 !== selectedDayIndex) {
          result = this.availableDays[selectedDayIndex + 1]
        }
        return result
      },

      /**
       * Get the closest previous available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null} day Day for which get sessions.
       *
       * @return {string} The closest previous available day with sessions.
       */
      getPrevAvailableDay (day) {
        const selectedDayIndex = this.availableDays.indexOf(day)
        let result = null
        if (selectedDayIndex !== 0) {
          result = this.availableDays[selectedDayIndex - 1]
        }
        return result
      },

      /**
       * Check that given date is disabled in datepicker.
       *
       * @since [*next-version*]
       *
       * @param {Date} date Date in datepicker to check.
       *
       * @return {boolean} Is given date is disabled.
       */
      isDateDisabled (date) {
        if (this.loading) {
          return true
        }
        const dateKey = this.createDateString(date)
        return Object.keys(this.daysWithSessions).indexOf(dateKey) === -1
      },

      /**
       * Get days of given session.
       *
       * @since [*next-version*]
       *
       * @param {BookingSession} session Booking session for getting days.
       *
       * @return {Date[]} List of days of given session.
       */
      _getSessionDays (session) {
        let days = []
        for (let m = this.createLocalDatetime(session.start); m.isBefore(this.createLocalDatetime(session.end)); m.add(1, 'days')) {
          days.push(m.toDate())
        }
        return days
      },

      /**
       * Get day key for given datetime. It will be used as as key for sessions.
       *
       * @since [*next-version*]
       *
       * @param {string|Date} value Value to get day key from.
       *
       * @return {string} Day key.
       */
      _getDayKey (value) {
        return this.createLocalDatetime(value).format(dateFormats.dayKey)
      }
    },

    components: {
      'datepicker': 'datepicker'
    }
  }
}