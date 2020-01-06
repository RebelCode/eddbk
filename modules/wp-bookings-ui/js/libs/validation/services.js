import ValidatorFactory from './ValidatorFactory'

/**
 * All validation services.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies Map of dependencies.
 * @param {object} applicationState Application state.
 *
 * @return {object}
 */
export default function (dependencies, applicationState) {
  const validators = applicationState ? applicationState.config['validators'] : {}
  let validatorsServicesDefinition = {}

  Object.keys(validators).forEach(name => {
    const validatorConfig = validators[name]
    validatorsServicesDefinition[name] = (container) => {
      return container.validatorFactory.make(validatorConfig)
    }
  })

  return Object.assign({}, validatorsServicesDefinition, {
    /**
     * Validation rules.
     *
     * @since [*next-version*]
     *
     * @return {Object.<string, Function>} Validation rules.
     */
    validationRules () {
      return dependencies.validate.Rules
    },

    /**
     * Validation error messages holder.
     *
     * @since [*next-version*]
     *
     * @return {{locale: string, getFieldMessage: Function}} Dictionary that hold validation messages.
     */
    validationDictionary () {
      return dependencies.validate.Validator.dictionary
    },

    /**
     * Validator factory instance.
     *
     * @since [*next-version*]
     *
     * @return {ValidatorFactory}
     */
    validatorFactory (container) {
      return new ValidatorFactory(container.validationRules, (field, rule, args) => {
        return container.validationDictionary.getFieldMessage(container.validationDictionary.locale, field, rule, [field, args])
      })
    },
  })
}