import { Transformer } from '@rebelcode/std-lib'

/**
 * Prepare data for working with booking in the UI.
 */
export default class BookingReadTransformer extends Transformer {
  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    /**
     * Create session field on model. We need to do it manually, because
     * we retrieving only some fields from API.
     *
     * @param {object} model Model to transform
     *
     * @return {object} Transformed model
     */
    service: (model) => {
      if (model.service) {
        model['session'] = {
          start: model.start,
          end: model.end,
          service: model.service.id,
          resources: model.resources.map(resource => resource.id)
        }
      }
      return model
    },
  }
}