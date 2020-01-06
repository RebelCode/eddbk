/**
 * Http promise-based client.
 *
 * @since [*next-version*]
 *
 * @typedef {Object} HttpClient
 *
 * @property {RequestHttpFunction} request Perform request to given URL
 */

/**
 * Request function in http client.
 *
 * @since [*next-version*]
 *
 * @function RequestHttpFunction
 *
 * @param {HttpRequestConfig} config Request configuration
 *
 * @return {Promise<any>}
 */

/**
 * Configuration object for request function.
 *
 * @since [*next-version*]
 *
 * @typedef {Object} HttpRequestConfig
 *
 * @property {String} method Http method
 * @property {String} url URL to send request on
 * @property {String|Object} [data] Data that will be sent
 */

/**
 * Rule to apply in transformer.
 *
 * @callback TransformerRuleCallback
 *
 * @since [*next-version*]
 *
 * @param {Object} model Model to transform
 *
 * @return {Object} Transformed model
 */

/**
 * Create datetime in a timezone.
 *
 * @since [*next-version*]
 *
 * @function CreateInTimezoneFunction
 *
 * @param {Date|string|moment} value Datetime that should be created in a timezone.
 * @param {string} tz String representing timezone, including UTC+${offset} format.
 *
 * @return {moment}
 */

/**
 * Parse datetime in a timezone.
 *
 * @since [*next-version*]
 *
 * @function ParseInTimezoneFunction
 *
 * @param {Date|string|moment} value Any value that moment can accept into constructor.
 * @param {string} tz String representing timezone, including UTC+${offset} format.
 *
 * @return {moment} Given datetime in a timezone (local time remains the same).
 */
