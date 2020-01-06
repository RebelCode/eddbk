/**
 * Component for selecting available booking session for given service.
 *
 * @usage
 * `<service-session-selector :service="service" v-model="session"></service-session-selector>`
 *
 * @since [*next-version*]
 *
 * @param {CreateDatetimeCapable} CreateDatetimeCapable Mixin that provides ability to work with datetime.
 * @param {SessionsApi} sessionsApi Session API wrapper, used for querying sessions.
 * @param {{
 *  dayKey: (string), // How to format day as a key,
 *  sessionTime: (string), // How to format session time,
 *  dayFull: (string), // How to format day for day heading,
 * }} dateFormats Map of date formats for formatting dates in UI.
 *
 * @return {object}
 */
export default function CfServiceSessionSelector (CreateDatetimeCapable, SessionsFilterCapable, sessionsApi, dateFormats) {
  return {
    template: '#service-session-selector-template',

    mixins: [ CreateDatetimeCapable, SessionsFilterCapable ],

    inject: {
      /**
       * Session length picker component, allows to select session.
       *
       * @since [*next-version*]
       */
      'session-time-picker': 'session-time-picker',

      /**
       * Date picker component, allows to select date of month.
       *
       * @since [*next-version*]
       */
      'session-date-picker': 'session-date-picker',

      /**
       * Date navigation component, for switching between days.
       *
       * @since [*next-version*]
       */
      'date-navigator': 'date-navigator',

      /**
       * Session transformer for transforming sessions for interacting with them in the UI.
       *
       * @since [*next-version*]
       */
      'sessionReadTransformer': 'sessionReadTransformer'
    },
    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {String|null} selectedDay Selected day to show sessions from.
         */
        selectedDay: null,

        /**
         * @since [*next-version*]
         *
         * @property {object[]} sessions List of sessions for current month.
         */
        sessions: [],

        /**
         * @since [*next-version*]
         *
         * @property {Boolean} isSessionsLoading Is sessions are loading right now.
         */
        isSessionsLoading: false,

        /**
         * @since [*next-version*]
         *
         * @property {Boolean} isEditModeAvailable Whether the session selector has selected value on component's init.
         */
        isEditModeAvailable: false,

        /**
         * @since [*next-version*]
         *
         * @property {Boolean} isEditing Whether the session selector is in edit mode.
         */
        isEditing: false,

        /**
         * @since [*next-version*]
         *
         * @property {object|null} oldValue Selected session that is being edited, passed via `v-model` in parent
         */
        oldValue: null,

        /**
         * @since [*next-version*]
         *
         * @property {object} preloadedSession Session that was chosen when component was created.
         */
        preloadedSession: null,

        /**
         * The previous closest available day with sessions.
         *
         * @since [*next-version*]
         *
         * @property {string|null}
         */
        prevAvailableDay: null,

        /**
         * The next closest available day with sessions.
         *
         * @since [*next-version*]
         *
         * @property {string|null}
         */
        nextAvailableDay: null,

        /**
         * Session list for selected day
         *
         * @since [*next-version*]
         *
         * @property {BookingSession[]}
         */
        selectedDaySessions: [],

        /**
         * @since [*next-version*]
         *
         * @property {Date} openedOnDate Date, on which datepicker is opened.
         */
        openedOnDate: this.createLocalDatetime().toDate(),

        /**
         * @since [*next-version*]
         *
         * @property {boolean} isMounted Whether component is mounted.
         */
        isMounted: false
      }
    },
    /**
     * Set isSeeding flag when component is mounted.
     *
     * @since [*next-version*]
     */
    mounted () {
      this.$nextTick(() => {
        this.isMounted = true
      })
    },
    watch: {
      /**
       * Watch by `service` property change and, if it's changed, refresh all selected values.
       * `immediate` option fire this watcher on component mount.
       *
       * @since [*next-version*]
       */
      service () {
        this.filter.duration = null
        this.filter.staffMember = null

        this.$nextTick(this._setCleanStateValues)
      },

      /**
       * Watch for selected sessions change, and if selected session duration is 1 day or more,
       * and day have only one session, select that session.
       *
       * @since [*next-version*]
       *
       * @param {BookingSession[]} sessions List of sessions for selected day.
       */
      selectedDaySessions (sessions) {
        if (this.isDailyDuration && sessions.length === 1) {
          this.session = sessions[0]
        }
      }
    },
    props: {
      /**
       * @since [*next-version*]
       *
       * @property {BookableService|null} service Selected service to choose sessions for.
       */
      service: {
        default: null
      },

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
       * @property {object|null} value Selected session, `v-model` in parent
       */
      value: {}
    },
    computed: {
      /**
       * Computed proxy for model. This is used to allow child components
       * change value of parent's model.
       *
       * @since [*next-version*]
       *
       * @property {object}
       */
      session: {
        /**
         * Model getter.
         *
         * @since [*next-version*]
         *
         * @return {object}
         */
        get () {
          return this.value
        },

        /**
         * Setter for model.
         *
         * @since [*next-version*]
         *
         * @param {object} newValue
         */
        set (newValue) {
          this.$emit('input', newValue)
        }
      },

      /**
       * @since [*next-version*]
       *
       * @property {String} Label for describing selected session in human readable format.
       */
      selectedSessionLabel () {
        if (!this.value) {
          return null
        }
        const sessionStart = this.createLocalDatetime(this.value.start)
        return sessionStart.format(dateFormats.sessionTime) + ', ' + sessionStart.format(dateFormats.dayFull)
      },

      /**
       * Is selected session duration more or equal to day.
       *
       * @since [*next-version*]
       *
       * @return {boolean}
       */
      isDailyDuration () {
        if (!this.filter.duration) {
          return false
        }
        return this.filter.duration >= 86400
      },
    },
    /**
     * Hook that would be triggered when component is created. Here
     * we are checking if value is already set, and if so we are in the
     * edit mode.
     *
     * @since [*next-version*]
     */
    created () {
      if (this.value) {
        this.isEditModeAvailable = true
        this.initShowMode()
      }
      else if (this.service) {
        this.$nextTick(() => this.loadSessions(this.openedOnDate))
      }
    },

    /**
     * Hook that would be triggered when component is destroyed. Here
     * we are cleaning sessions cache to give user appropriate sessions
     * after component re-creation.
     *
     * @since [*next-version*]
     */
    destroyed () {
      sessionsApi.clearCache()
    },

    methods: {
      /**
       * Init "show" session selector mode. It is used for showing selected session, but it
       * doesn't allow to change selected session until user entered in "Edit" mode.
       *
       * @since [*next-version*]
       */
      initShowMode () {
        this.preloadedSession = this.sessionReadTransformer.transform(this.value)
        this.selectFilters(this.preloadedSession)

        this.sessions = [this.preloadedSession]

        this.$nextTick(() => {
          const sessionStart = this.createLocalDatetime(this.preloadedSession.start)
          this.selectedDay = this.createDateString(sessionStart)
          this.openedOnDate = this.createDateString(sessionStart)
        })
      },

      /**
       * When the picker in the edit mode (so session is preloaded), this allows
       * to edit that session time.
       *
       * @since [*next-version*]
       */
      startEdit () {
        this.isEditing = true
        this.oldValue = Object.assign({}, this.value)

        const sessionStart = this.createLocalDatetime(this.preloadedSession.start)

        this.loadSessions(sessionStart).then(() => {
          this.selectedDay = null
          this.$nextTick(() => {
            this.selectedDay = this.createDateString(sessionStart)
          })
        })
      },

      /**
       * Revert edit and enable "show" mode.
       *
       * @since [*next-version*]
       */
      cancelEdit () {
        if (this.isSessionsLoading) {
          return
        }

        this.isEditing = false
        this.$emit('input', this.oldValue)

        this.$nextTick(this.initShowMode)
      },

      /**
       * Add preloaded session to all sessions to work with them.
       *
       * @since [*next-version*]
       *
       * @param {object} sessions All sessions.
       * @param {object} preloadedSession Session that was selected when picker was opened.
       *
       * @return {*}
       */
      _addPreloadedSession (sessions, preloadedSession = null) {
        if (!preloadedSession) {
          return sessions
        }

        const preselectedInFetched = sessions.find(session => {
          return this._sessionsIsSame(session, preloadedSession)
        })

        if (preselectedInFetched) {
          return sessions
        }

        sessions.push(preloadedSession)
        return sessions
      },

      /**
       * Check that sessions are the same.
       *
       * @since [*next-version*]
       *
       * @param {BookingSession} a First session to check
       * @param {BookingSession} b Second session to check
       *
       * @return {boolean}
       */
      _sessionsIsSame (a, b) {
        return a.service === b.service &&
          a.resource === b.resource &&
          a.start === b.start &&
          a.end === b.end
      },

      /**
       * Clean old values and set new, clean ones to select session.
       *
       * @since [*next-version*]
       */
      _setCleanStateValues () {
        this.selectedDay = null
        this.sessions = []
        this.isEditModeAvailable = false
        this.isEditing = false

        this.$nextTick(() => {
          if (this.service) {
            this.loadSessions(this.openedOnDate)
          }
        })
      },

      /**
       * Load sessions from API for given month.
       *
       * @since [*next-version*]
       *
       * @param {Date} date Date for which sessions should be loaded.
       *
       * @return {Promise<any>}
       */
      loadSessions (date) {
        this.isSessionsLoading = true
        return sessionsApi.fetch(this._prepareSessionRequestParams(date)).then(sessions => {
          this.sessions = this._addPreloadedSession(sessions, this.preloadedSession)
          this.isSessionsLoading = false
        }, error => {
          console.error(error)
          this.isSessionsLoading = false
        })
      },

      /**
       * Get params for retrieving sessions for month.
       *
       * @since [*next-version*]
       *
       * @param {Date} date Date for which sessions should be loaded.
       *
       * @return {{service: Number, start: (string), end: (string)}}
       */
      _prepareSessionRequestParams (date) {
        let { start, end } = this._getDateRange(date)
        return {
          service: this.service.id,
          start,
          end
        }
      },

      /**
       * Get date range from given date.
       *
       * @since [*next-version*]
       *
       * @param {Date} date Date for which sessions should be loaded.
       *
       * @return {{start: string, end: string}} Date range for given date, formatted in ISO8601.
       */
      _getDateRange (date) {
        const currentDay = this.createDatetime()

        const firstDayOfRange = this.createSameLocalDatetime(date).startOf('month')
        const lastDayOfRange = this.createSameLocalDatetime(date).endOf('month')

        const start = (currentDay.isAfter(firstDayOfRange) ? currentDay : firstDayOfRange).startOf('day').format()
        const end = lastDayOfRange.endOf('day').format()

        return {
          start,
          end
        }
      }
    },
    components: {
      'session-time-picker': 'session-time-picker',
      'session-date-picker': 'session-date-picker',
      'date-navigator': 'date-navigator'
    }
  }
}
