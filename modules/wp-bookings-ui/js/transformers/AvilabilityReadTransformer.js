import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to availability before manipulating with it in the UI.
 *
 * @class AvailabilityReadTransformer
 */
export default class AvailabilityReadTransformer extends Transformer {
  /**
   * AvailabilityReadTransformer constructor.
   *
   * @param {Function} moment Moment JS.
   * @param {Function} transformDatetimeForUi Function that transform datetime from state to format used in UI.
   */
  constructor (moment, transformDatetimeForUi) {
    super()
    this.moment = moment
    this.transformDatetimeForUi = transformDatetimeForUi
  }

  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    start: (model, { timezone }) => {
      model['start'] = this.transformDatetimeForUi(model['start'], timezone)
      return model
    },
    end: (model, { timezone }) => {
      model['end'] = this.transformDatetimeForUi(model['end'], timezone)
      return model
    },
    repeatUntilDate: (model, { timezone }) => {
      if (model['repeatUntilDate']) {
        model['repeatUntilDate'] = this.transformDatetimeForUi(model['repeatUntilDate'], timezone)
      }
      return model
    },
    excludeDates: (model, { timezone }) => {
      model['excludeDates'] = model['excludeDates'].map(excludeDate => {
        return this.transformDatetimeForUi(excludeDate, timezone)
      })
      return model
    }
  }
}