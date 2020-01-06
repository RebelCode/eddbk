/**
 * Filter for getting sessions using the staff member's id.
 *
 * @since [*next-version*]
 */
export default {
  watch: {
    /**
     * Watch for filter's value change and select first values for the next filters.
     *
     * @since [*next-version*]
     *
     * @param {string} newValue
     * @param {string} oldValue
     */
    'filter.staffMember' (newValue, oldValue) {
      if (!newValue || !oldValue) {
        return
      }
      const filtersAfterCurrent = this.filters.slice(this.filters.indexOf('staffMember') + 1)
      for (const key of filtersAfterCurrent) {
        this.filter[key] = Object.keys(this[`${key}FilterValues`])[0]
      }
    },
  },
  computed: {
    /**
     * @var {object<string, string>} staffMemberFilterValues List of values for staff members.
     *
     * @since [*next-version*]
     */
    staffMemberFilterValues () {
      if (!this.service) {
        return []
      }

      let staff = []

      for (let sessionType of this.service.sessionTypes.filter(sessionType => this.filterAgainstPreviousFilters('staffMember', sessionType))) {
        let staffMembers = (sessionType.data.resources || []).filter(resource => resource.type === 'staff')
        for (let staffMember of staffMembers) {
          if (!staff.find(member => member.id === staffMember.id)) {
            staff.push(staffMember)
          }
        }
      }
      return staff
        .reduce((acc, staffMember) => {
          acc[staffMember.id] = staffMember.name
          return acc
        }, {})
    },

    /**
     * Staff member exposed value for outside consuming.
     *
     * @since [*next-version*]
     *
     * @return {*}
     */
    staffMemberExposedValue () {
      if (!this.filter.staffMember) {
        return false
      }
      for (let sessionType of this.service.sessionTypes) {
        let foundStaffMember = sessionType.data.resources.find(resource => Number(resource.id) === Number(this.filter.staffMember))
        if (!!foundStaffMember) {
          return foundStaffMember
        }
      }
      return false
    },
  },
  methods: {
    /**
     * Check whether the session passes the staff member filter.
     *
     * @since [*next-version*]
     *
     * @param {BookingSession} session Session to check.
     *
     * @return {boolean} Whether the session passes the staff member filter.
     */
    staffMemberFilterPassed (session) {
      return session.resources.indexOf(this.filter.staffMember) > -1
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
    staffMemberFilterCorrespondsToSession (filterValue, session) {
      return session.resources.indexOf(filterValue) > -1
    },

    /**
     * Check whether the session type corresponds to the filter's value.
     *
     * @since [*next-version*]
     *
     * @param {string} resourceId
     * @param {ServiceSessionType} sessionType
     *
     * @return {boolean}
     */
    staffMemberInSessionType (resourceId, sessionType) {
      resourceId = Number(resourceId)
      return !!sessionType.data.resources.find(resource => Number(resource.id) === resourceId && resource.type === 'staff')
    },
  }
}
