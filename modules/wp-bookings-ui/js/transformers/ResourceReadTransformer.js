import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to the resource before displaying it in the UI.
 *
 * @class ResourceReadTransformer
 */
export default class ResourceReadTransformer extends Transformer {
  /**
   * ResourceReadTransformer constructor.
   *
   * @param {AvailabilityReadTransformer} availabilityReadTransformer Transformer for changing availability for saving.
   */
  constructor (availabilityReadTransformer) {
    super()
    this.availabilityReadTransformer = availabilityReadTransformer
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
  }
}
