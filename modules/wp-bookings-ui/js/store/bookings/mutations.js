/**
 * Mutations for booking-options feature
 */
const mutations = {
  /**
   * Seed booking editor by passed model.
   *
   * @param state
   * @param model
   */
  setBookingEditorState (state, model) {
    state.bookingModel = model
  },

  /**
   * Set services to chose from and for filtering
   *
   * @param state
   * @param data
   */
  setServices (state, data) {
    state.services = data
  },

  /**
   * Set bookings
   *
   * @param state
   * @param data
   */
  setBookings (state, data) {
    state.bookings = data
  },

  /**
   * Set statuses data from backend response.
   *
   * This data holds statuses title and count of records with given status.
   *
   * @param state
   * @param statuses
   */
  setBookingsStatuses (state, statuses) {
    state.statuses = statuses
  },

  /**
   * Set bookings count
   *
   * @param state
   * @param count
   */
  setBookingsCount (state, count) {
    state.bookingsCount = count
  },

  /**
   * Update bookings filter
   * 
   * @param state
   * @param key
   * @param value
   */
  setBookingsFilter (state, { key, value }) {
    state.filter[key] = value
  },

  /**
   * Store last request parameters in state.
   *
   * @param {object} state
   * @param {object} lastRequestParameters
   */
  setLastRequestParameters (state, lastRequestParameters) {
    state.lastRequestParameters = lastRequestParameters
  },

  /**
   * Set timezone for bookings page.
   *
   * @since [*next-version*]
   *
   * @param {object} state Module's state.
   * @param {string} value Timezone name.
   */
  setTimezone (state, value) {
    state.timezone = value
  }
}

export default mutations