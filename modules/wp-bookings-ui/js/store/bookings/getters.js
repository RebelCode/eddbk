/**
 * Bookings feature getters.
 */
export default {
  /**
   * Last request parameters getter.
   *
   * @since [*next-version*]
   *
   * @param {object} state Bookings state.
   *
   * @return {Function} Getter of last request params.
   */
  getLastRequestParameters: (state) => () => {
    return state.lastRequestParameters
  }
}