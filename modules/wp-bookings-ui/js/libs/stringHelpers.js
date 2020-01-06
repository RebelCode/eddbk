/**
 * Helping methods to work with strings.
 *
 * @since [*next-version*]
 *
 * @return {object}
 */
export function makeStringHelpers () {
  return {
    /**
     * Capitalize the string.
     *
     * @since [*next-version*]
     *
     * @param {string} string The string to capitalize.
     *
     * @return {string} Capitalized string.
     */
    capitalize (string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    }
  }
}
