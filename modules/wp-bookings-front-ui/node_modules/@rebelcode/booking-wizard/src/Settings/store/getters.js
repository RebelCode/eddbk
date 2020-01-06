export default {
  /**
   * Whether monday is the first day in calendar.
   *
   * @param {{weekStartsOn: string}} state Settings state.
   *
   * @return {boolean} Whether monday is the first day in calendar.
   */
  mondayFirst (state) {
    return state.weekStartsOn === 'monday'
  }
}
