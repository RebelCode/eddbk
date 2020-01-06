export function bookingHelpers (statusesColors, statusesLabels) {
  return {
    /**
     * Get status style for booking
     *
     * @param status
     * @return {{'background-color': *, color: string}}
     */
    statusStyle (status) {
      return {
        'background-color': statusesColors[status],
        'color': '#000'
      }
    },

    /**
     * Get label for status
     *
     * @param status
     * @return {*}
     */
    statusLabel (status) {
      return statusesLabels[status]
    }
  }
}