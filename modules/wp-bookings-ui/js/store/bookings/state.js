const state = {
  /**
   * Booking model that will be used for passing data from
   * calendar or list view to the modal
   */
  bookingModel: {},

  /**
   * List of all available services
   */
  services: [],

  /**
   * List of all bookings.
   */
  bookings: [],

  /**
   * Statuses for bookings.
   */
  statuses: [],

  /**
   * Count of all bookings.
   */
  bookingsCount: 0,

  /**
   * Bookings filter that remain the same when user change view
   */
  filter: {
    status: 'all',
    search: null,
    service: null,
  },

  /**
   * @property {object|null} Last request parameters that was used for filtering bookings.
   */
  lastRequestParameters: {},

  /**
   * @property {string} timezone Timezone on bookings page
   */
  timezone: 'UTC+0'
}

export default state