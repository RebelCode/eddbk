import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to sessions before any interaction in UI.
 *
 * @class SessionReadTransformer
 */
export default class SessionReadTransformer extends Transformer {
  /**
   * Session read transformer constructor.
   *
   * @param {Function} moment Moment JS.
   */
  constructor (moment) {
    super()
    this.moment = moment
  }

  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    /**
     * Add duration field to model.
     *
     * @param {object} model Model to transform.
     *
     * @return {*}
     */
    start: (model) => {
      const startUnix = this.moment(model.start).unix()

      model['duration'] = this.moment(model.end).unix() - startUnix
      model['startUnix'] = startUnix

      return model
    },
    /**
     * Transform service to Number.
     *
     * @param {object} model Model to transform.
     *
     * @return {*}
     */
    service: (model) => {
      model['service'] = parseInt(model['service'])
      return model
    },
    /**
     * Transform resource to Number.
     *
     * @param {object} model Model to transform.
     *
     * @return {*}
     */
    resource: (model) => {
      model['resource'] = parseInt(model['resource'])
      return model
    }
  }
}