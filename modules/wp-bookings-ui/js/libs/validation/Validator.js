import ValidationResult from './ValidationResult'
import deepGet from 'lodash/get'

/**
 * Validates data and gives list of errors if data is not valid.
 *
 * @since [*next-version*]
 *
 * @class Validator
 */
export default class Validator {
  /**
   * Validator constructor.
   *
   * @since [*next-version*]
   *
   * @param {object} config Validator configuration.
   * @param {Object.<string, Function>} rules List of rules.
   * @param {Function} getErrorMessage Function for retrieving error message.
   */
  constructor (config, rules, getErrorMessage) {
    this.config = config
    this.rules = rules
    this.getErrorMessage = getErrorMessage
  }

  /**
   * Validate data.
   *
   * @since [*next-version*]
   * 
   * @param {object} data Data that should be validated.
   *
   * @return {Promise<ValidationResult>} Promise that holds validation result.
   */
  validate (data) {
    return new Promise((resolve) => {
      let errorsBag = {}

      this.config.forEach((validationConfig) => {
        const field = validationConfig.field
        const rule = this.rules[validationConfig.rule]
        const value = Array.isArray(validationConfig.value) ? validationConfig.value : [validationConfig.value]
        const result = rule(deepGet(data, field), value)

        if (!result && !errorsBag[field]) {
          errorsBag[field] = []
        }

        if (!result) {
          errorsBag[field].push(this.getErrorMessage(field, validationConfig.rule, value))
        }
      })

      resolve(new ValidationResult(errorsBag))
    })
  }
}