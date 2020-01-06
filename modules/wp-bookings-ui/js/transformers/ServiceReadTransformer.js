import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to the service before any interactions in the UI.
 *
 * @class ServiceReadTransformer
 */
export default class ServiceReadTransformer extends Transformer {
  /**
   * ServiceReadTransformer constructor.
   *
   * @param {AvailabilityReadTransformer} availabilityReadTransformer Transformer for changing availability to using in UI.
   * @param {sessionTypeReadTransformer} sessionTypeReadTransformer Transforms session length data to use it in the UI.
   */
  constructor (availabilityReadTransformer, sessionTypeReadTransformer) {
    super()
    this.availabilityReadTransformer = availabilityReadTransformer
    this.sessionTypeReadTransformer = sessionTypeReadTransformer
  }

  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    availability: (model, { timezone }) => {
      model.availability['rules'] = model.availability.rules.map(availability => {
        return this.availabilityReadTransformer.transform(availability, { timezone })
      })
      return model
    },

    sessionTypes: (model) => {
      model['sessionTypesStored'] = model.sessionTypes
      model['sessionTypes'] = model.sessionTypes.map(sessionType => {
        return this.sessionTypeReadTransformer.transform(sessionType)
      })
      return model
    }
  }
}
