import ApiErrorHandler from './ApiErrorHandler'

/**
 * Error handler that get main error message from response.
 *
 * @since [*next-version*]
 *
 * @class GeneralApiErrorHandler
 */
export default class GeneralApiErrorHandler extends ApiErrorHandler {
  /**
   * Get general error message.
   *
   * @since [*next-version*]
   *
   * @param {Response} response
   *
   * @return {string|boolean} Error message that describe what happen in general.
   */
  _getError (response) {
    const responseData = response.data
    if (responseData.data && responseData.data.errors && responseData.data.errors.length) {
      return responseData.data.errors[0]
    }
    else if (responseData.message) {
      return responseData.message
    }
    return false
  }
}