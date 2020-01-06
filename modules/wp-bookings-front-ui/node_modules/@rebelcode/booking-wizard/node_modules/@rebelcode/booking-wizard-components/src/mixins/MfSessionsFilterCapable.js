import DurationFilter from './filters/DurationFilter'
import StaffMembersFilter from './filters/StaffMembersFilter'

export default function MfSessionsFilterCapable () {
  return {
    mixins: [ DurationFilter, StaffMembersFilter ],
    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {object} sessionFilters List of filter values applied to the sessions list.
         */
        filter: {
          duration: null,
          staffMember: null
        }
      }
    },
    props: {
      /**
       * Ordered list of filters.
       *
       * @since [*next-version*]
       */
      filters: {
        default () {
          return [ 'duration', 'staffMember' ]
        }
      }
    },
    watch: {
      /**
       * Watch by `service` property change and, if it's changed, select first available
       * session length. `immediate` option fire this watcher on component mount.
       *
       * @since [*next-version*]
       */
      service: {
        immediate: true,
        handler () {
          const currentFilters = JSON.parse(JSON.stringify(this.filter))

          const areFiltersEmpty = (f) => {
            return this.filters.reduce((isEmpty, key) => {
              return isEmpty && !f[key]
            }, true)
          }

          this.$nextTick(() => {
            const nextFilters = JSON.parse(JSON.stringify(this.filter))

            if (areFiltersEmpty(currentFilters) && !areFiltersEmpty(nextFilters)) {
              return
            }

            for (const key of this.filters) {
              if (!this[`${key}FilterValues`]) {
                continue
              }
              this.filter[key] = Object.keys(this[`${key}FilterValues`])[0]
            }
          })
        }
      },

      /**
       * Unselect day and session when filter is changed.
       *
       * @since [*next-version*]
       */
      filter: {
        deep: true,
        handler () {
          if (!this.isMounted) {
            return
          }
          this.session = null

          this.$nextTick(() => {
            const selectedDay = this.selectedDay
            this.selectedDay = null
            if (this.hasSessionsOnSelectedDay()) {
              this.$nextTick(() => {
                this.selectedDay = selectedDay
              })
            }
          })

          this.emitExposedValues()
        }
      },
    },
    computed: {
      /**
       * @var {BookingSession[]} filteredSessions List of sessions that passes all filters.
       *
       * @since [*next-version*]
       */
      filteredSessions () {
        return this.sessions.filter(session => {
          return Object.keys(this.filter).reduce((acc, key) => {
            return acc && (!!this.filter[key] ? this[`${key}FilterPassed`](session) : true)
          }, true)
        })
      }
    },
    methods: {
      /**
       * Emit exposed filter values for further usage.
       *
       * @since [*next-version*]
       */
      emitExposedValues () {
        let exposedFilters = {}
        for (let filter of this.filters) {
          exposedFilters[filter] = this[`${filter}ExposedValue`]
        }
        this.$emit('exposed', exposedFilters)
      },

      /**
       * Whether there are some sessions on selected day.
       *
       * @since [*next-version*]
       *
       * @return {boolean}
       */
      hasSessionsOnSelectedDay () {
        return !!this.selectedDaySessions.length
      },

      /**
       * Get all possible values for the filter.
       *
       * @since [*next-version*]
       *
       * @param {string} filter The filter's name.
       *
       * @return {object<string, string>} Possible values of the filter.
       */
      getFilterValues (filter) {
        return this[`${filter}FilterValues`]
      },

      /**
       * Whether the filter has possible values.
       *
       * @since [*next-version*]
       *
       * @param {string} filter The filter's name.
       *
       * @return {boolean}
       */
      hasFilterValues (filter) {
        return !!Object.keys(this[`${filter}FilterValues`]).length
      },

      /**
       * Filter session type against previous filters.
       *
       * @since [*next-version*]
       *
       * @param {string} filterType Filter's name.
       * @param {ServiceSessionType} sessionType The session type.
       *
       * @return {boolean}
       */
      filterAgainstPreviousFilters (filterType, sessionType) {
        const currentFilterPosition = this.filters.indexOf(filterType)
        const filtersBefore = this.filters.slice(0, currentFilterPosition)

        for (let checkingFilter of filtersBefore) {
          const values = !!Object.keys(this[`${checkingFilter}FilterValues`]).length
          if (!values) {
            return true
          }
          let checkResult = !!this[`${checkingFilter}InSessionType`] ? this[`${checkingFilter}InSessionType`](this.filter[checkingFilter], sessionType) : true
          if (!checkResult) {
            return false
          }
        }
        return true
      },

      /**
       * Select filters that correspond to given session for given service.
       *
       * @since [*next-version*]
       *
       * @param {BookingSession} session
       */
      selectFilters (session) {
        for (const key of this.filters) {
          if (!this[`${key}FilterValues`] || !Object.keys(this[`${key}FilterValues`]).length) {
            continue
          }
          this.filter[key] = Object.keys(this[`${key}FilterValues`]).find(value => this[`${key}FilterCorrespondsToSession`](value, session))
        }
      }
    }
  }
}
