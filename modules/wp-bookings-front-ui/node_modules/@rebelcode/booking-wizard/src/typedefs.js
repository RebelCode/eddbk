/**
 * Object that is capable of selecting data from some source.
 *
 * @since [*next-version*]
 *
 * @typedef {Object} SelectCapable
 *
 * @method {CreateFunction} create Method for creating data model.
 */

/**
 * Object that is capable of creating data.
 *
 * @since [*next-version*]
 *
 * @typedef {Object} CreateCapable
 *
 * @method {SelectFunction} select Method for selecting data model.
 */

/**
 * Function for creating data.
 *
 * @since [*next-version*]
 *
 * @function CreateFunction
 *
 * @property {object} data Data that should be created.
 *
 * @return {Promise<any>} Data creating promise.
 */

/**
 * Function for selecting data.
 *
 * @since [*next-version*]
 *
 * @function SelectFunction
 *
 * @property {object} params Params for selecting data.
 *
 * @return {Promise<any>} Data selecting promise.
 */