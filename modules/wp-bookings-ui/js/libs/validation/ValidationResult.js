/**
 * Validation result that holds boolean result of validation
 * and list of errors that occurred during validation.
 *
 * @since [*next-version*]
 *
 * @class ValidationResult
 */
export default class ValidationResult {
  /**
   * ValidationResult constructor.
   *
   * @since [*next-version*]
   *
   * @param {Object.<string, string[]>} errorsBag Map of field names to lists of corresponding errors (if there are some).
   */
  constructor (errorsBag = {}) {
    this.errorsBag = errorsBag
  }

  /**
   * Whether result of validation is successful.
   *
   * @since [*next-version*]
   *
   * @return {boolean}
   */
  get valid () {
    return Object.keys(this.errorsBag).length === 0
  }

  /**
   * Check that field has some validation error.
   *
   * @since [*next-version*]
   *
   * @param {string} field Field to check.
   *
   * @return {boolean} Whether this field has validation error.
   */
  hasErrors (field) {
    return !!this.errorsBag[field]
  }

  /**
   * Remove field errors is that field has some validation errors.
   *
   * @since [*next-version*]
   *
   * @param {string} field Field to clean.
   */
  removeErrors (field) {
    if (this.hasErrors(field)) {
      delete this.errorsBag[field]
    }
  }

  /**
   * Get field validation errors.
   *
   * @since [*next-version*]
   *
   * @param {string} field Field to get errors for.
   *
   * @return {string[]} Errors messages for field.
   */
  getErrors (field) {
    return this.errorsBag[field]
  }

  /**
   * Get all validation errors.
   *
   * @since [*next-version*]
   *
   * @return {Object.<string, string[]>} List of fields and corresponding errors (if there are some).
   */
  getAllErrors () {
    return this.errorsBag
  }
}