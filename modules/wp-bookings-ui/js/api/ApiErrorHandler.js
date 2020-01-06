/**
 * Class for handling API errors. Its responsibility is to
 * get errors from API response.
 *
 * @since [*next-version*]
 *
 * @class ApiErrorHandler
 */
export default class ApiErrorHandler {
  /**
   * ApiErrorHandler constructor.
   *
   * @since [*next-version*]
   *
   * @param {ApiErrorHandlingFunction} handler Handler function that will process errors found in response.
   */
  constructor (handler) {
    this.handler = handler
  }

  /**
   * Handle error response.
   *
   * @since [*next-version*]
   *
   * @param {Error} error Error that occurred on booking saving.
   */
  handle (error) {
    const errorResponse = error.response
    if (!errorResponse) {
      return
    }
    this.handler(this._getError(errorResponse))
  }

  /**
   * Get error from response.
   *
   * @since [*next-version*]
   *
   * @param {Response} response
   *
   * @return {object|string} Bag of errors or one error from response.
   */
  _getError (response) {
    throw new Error('Implement `_getError()` method')
  }
}
