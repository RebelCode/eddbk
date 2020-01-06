import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to session length before any state interaction in UI.
 *
 * @class SessionTypeReadTransformer
 */
export default class SessionTypeReadTransformer extends Transformer {
  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    price: (model) => {
      model['price'] = model.price.amount
      return model
    }
  }
}