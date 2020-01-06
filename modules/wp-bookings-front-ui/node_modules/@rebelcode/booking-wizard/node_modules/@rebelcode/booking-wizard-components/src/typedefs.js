/**
 * Booking session object.
 *
 * @typedef {Object} BookingSession
 *
 * @property {number} duration Session duration in seconds
 * @property {number} startUnix Session start time in unix timestamp
 * @property {string} start Session start datetime in ISO8601 format
 * @property {string} end Session start datetime in ISO8601 format
 * @property {number} service Session's service ID
 * @property {number} resource Session's resource ID
 */

/**
 * Booking session object in API store, available for querying.
 *
 * @typedef {BookingSession} BookingStoredSession
 *
 * @property {number} endUnix Session end time in unix timestamp
 */

/**
 * Service that can be booked.
 *
 * @typedef {Object} BookableService
 *
 * @property {number} id Service identifier
 * @property {ServiceSessionType[]} sessionTypes List of available session's types for the given service.
 */

/**
 * Available duration of booking session.
 *
 * @typedef {Object} ServiceSessionType
 *
 * @property {number} data.duration Length of session in seconds.
 */

/**
 * Object that represents range for booking sessions.
 *
 * @typedef {Object} BookingSessionsRange
 *
 * @param {number} service Service ID.
 * @param {string} start Range start datetime in ISO8601.
 * @param {string} end Range end datetime in ISO8601
 */

/**
 * Function for creating datetime in some timezone.
 *
 * @function CreateDatetimeFunction
 *
 * @param {string|Date} value Any value that should be used for creating date.
 * @param {string} timezone Timezone in which date should be created
 */

/**
 * Function for creating datetime in local timezone.
 *
 * @function CreateLocalDatetimeFunction
 *
 * @param {string|Date} value Any value that should be used for creating date.
 */

/**
 * Provide ability to create datetime objects.
 *
 * @class CreateDatetimeCapable
 *
 * @method {CreateDatetimeFunction} createDatetime
 * @method {CreateLocalDatetimeFunction} createLocalDatetime
 */