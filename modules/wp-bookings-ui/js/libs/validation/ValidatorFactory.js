import Validator from './Validator'

/**
 * Validator factory.
 *
 * @since [*next-version*]
 *
 * @class ValidatorFactory
 */
export default class ValidatorFactory {
  /**
   * ValidatorFactory constructor.
   *
   * @since [*next-version*]
   *
   * @param {Object.<string, Function>} rules List of rules.
   * @param {Function} getErrorMessage Function for retrieving error message.
   */
  constructor (rules, getErrorMessage) {
    this.rules = rules
    this.getErrorMessage = getErrorMessage
  }

  /**
   * Make new validator instance.
   *
   * @param {object} config Validator configuration.
   *
   * @return {Validator} New validator.
   */
  make (config) {
    return new Validator(config, this.rules, this.getErrorMessage)
  }
}