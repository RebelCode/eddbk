/**
 * Class for changing object in some way using list of rules
 * that applied to given object in order.
 *
 * @since [*next-version*]
 *
 * @class Transformer
 */
export class Transformer {
  constructor () {
    /**
     * List of rules to transform.
     *
     * @since [*next-version*]
     *
     * @type {Object.<string, TransformerRuleCallback>}
     */
    this.rules = {}
  }

  /**
   * Transform given model according rules.
   *
   * @since [*next-version*]
   *
   * @param {object} model Some model to transform
   * @param {object} payload Additional data to use while transformation
   *
   * @return {object} Transformed model
   */
  transform (model, payload = {}) {
    for (let sourceField of Object.keys(this.rules)) {
      const handler = this.rules[sourceField]
      if (!model.hasOwnProperty(sourceField)) {
        continue
      }
      model = Object.assign({}, model)
      model = handler(model, payload)
    }
    return model
  }
}