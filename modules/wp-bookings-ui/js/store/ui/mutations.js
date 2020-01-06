/**
 * Mutations for UI-related stuff
 */
const mutations = {
  setAvailabilityModalVisibility (state, newVisibility) {
    state.availabilityModalVisible = newVisibility
  },
  setBookingModalVisibility (state, newVisibility) {
    state.bookingModalVisible = newVisibility
  },
  setBookingsIsLoading (state, isLoading) {
    state.bookings.isLoading = isLoading
  },
}

export default mutations