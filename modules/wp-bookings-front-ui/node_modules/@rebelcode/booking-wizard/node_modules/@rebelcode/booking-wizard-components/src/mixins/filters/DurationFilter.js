/**
 * Filter for getting sessions using the duration.
 *
 * @since [*next-version*]
 */
export default {
  inject: [
    'humanizeDuration'
  ],
  watch: {
    /**
     * Watch for filter's value change and select first values for the next filters.
     *
     * @since [*next-version*]
     *
     * @param {string} newValue
     * @param {string} oldValue
     */
    'filter.duration' (newValue, oldValue) {
      if (!newValue || !oldValue) {
        return
      }
      const filtersAfterCurrent = this.filters.slice(this.filters.indexOf('duration') + 1)
      for (const key of filtersAfterCurrent) {
        this.filter[key] = Object.keys(this[`${key}FilterValues`])[0]
      }
    }
  },
  computed: {
    /**
     * @var {number|boolean} selectedSessionDuration Selected session duration.
     *
     * @since [*next-version*]
     */
    selectedSessionDuration () {
      if (!this.filter.duration) {
        return false
      }
      const selectedSession = this.service.sessionTypes.find(sessionType => this._getSessionTypeId(sessionType) === this.filter.duration)
      return selectedSession ? selectedSession.data.duration : false
    },

    /**
     * @var {object<string, string>} durationFilterValues List of values for session duration.
     *
     * @since [*next-version*]
     */
    durationFilterValues () {
      if (!this.service) {
        return []
      }

      const sessionTypeLabel = sessionType => {
        const duration = this.humanizeDuration(sessionType.data.duration * 1000, {
          units: ['w', 'd', 'h', 'm'],
          round: true
        })
        return !!sessionType.label ? `${sessionType.label} (${duration})` : duration
      }

      return this.service.sessionTypes
        .filter(sessionType => this.filterAgainstPreviousFilters('duration', sessionType))
        .reduce((acc, sessionType) => {
          acc[this._getSessionTypeId(sessionType)] = sessionTypeLabel(sessionType)
          return acc
        }, {})
    },

    /**
     * Duration exposed value for outside consuming.
     *
     * @since [*next-version*]
     *
     * @return {*}
     */
    durationExposedValue () {
      if (!this.filter.duration) {
        return false
      }
      return this.service.sessionTypes.find(sessionType => this._getSessionTypeId(sessionType) === this.filter.duration)
    },
  },
  methods: {
    _getSessionTypeId (sessionType) {
      return sessionType.label + String(sessionType.data.duration)
    },

    /**
     * Check whether the session passes the duration filter.
     *
     * @since [*next-version*]
     *
     * @param {BookingSession} session Session to check.
     *
     * @return {boolean} Whether the session passes the duration filter.
     */
    durationFilterPassed (session) {
      return session.duration === this.selectedSessionDuration
    },

    /**
     * Check whether the session satisfies filter value.
     *
     * @since [*next-version*]
     *
     * @param {string} filterValue
     * @param {BookingSession} session
     *
     * @return {boolean}
     */
    durationFilterCorrespondsToSession (filterValue, session) {
      const foundSessionType = this.service.sessionTypes.find(trSessionType => this._getSessionTypeId(trSessionType) === filterValue)
      if (!foundSessionType) {
        return false
      }
      return foundSessionType.data.duration === session.duration && this._matchesSessionResources(foundSessionType, session)
    },

    /**
     * Check whether the session type resources matches session resources.
     *
     * @since [*next-version*]
     *
     * @param sessionType
     * @param session
     *
     * @return {boolean}
     */
    _matchesSessionResources (sessionType, session) {
      let sessionTypeResources = sessionType.data.resources.map(resource => resource.id)

      return sessionTypeResources.reduce((res, sessionTypeResourceId) => {
        return res && session.resources.indexOf(sessionTypeResourceId) > -1
      }, true)
    },

    /**
     * Check whether the session type corresponds to the filter's value.
     *
     * @since [*next-version*]
     *
     * @param {string} sessionTypeId
     * @param {ServiceSessionType} sessionType
     *
     * @return {boolean}
     */
    durationInSessionType (sessionTypeId, sessionType) {
      const foundSessionType = this.service.sessionTypes.find(trSessionType => this._getSessionTypeId(trSessionType) === sessionTypeId)
      if (!foundSessionType) {
        return false
      }
      return this._getSessionTypeId(sessionType) === this._getSessionTypeId(foundSessionType)
    },
  }
}
