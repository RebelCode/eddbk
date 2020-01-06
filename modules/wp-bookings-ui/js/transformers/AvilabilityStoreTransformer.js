import { Transformer } from '@rebelcode/std-lib'

/**
 * Transformer that applied to availability before storing it.
 *
 * @class AvailabilityStoreTransformer
 */
export default class AvailabilityStoreTransformer extends Transformer {
  /**
   * AvailabilityStoreTransformer constructor.
   *
   * @param {Function} moment Moment JS.
   * @param {Function} transformDatetimeForStore Function that transform datetime from UI to format in store.
   */
  constructor (moment, transformDatetimeForStore) {
    super()
    this.moment = moment
    this.transformDatetimeForStore = transformDatetimeForStore
  }

  /**
   * Rules that will be applied in order for model.
   *
   * @property {Object.<string, TransformerRuleCallback>} rules
   */
  rules = {
    id: (model) => {
      if (model.id[0] === '_') {
        model['id'] = null
      }
      return model
    },
    start: (model, { timezone }) => {
      model['start'] = this.transformDatetimeForStore(model['start'], timezone)
      return model
    },
    end: (model, { timezone }) => {
      model['end'] = this.transformDatetimeForStore(model['end'], timezone)
      return model
    },
    repeatUntilDate: (model, { timezone }) => {
      if (model['repeatUntilDate']) {
        model['repeatUntilDate'] = this.transformDatetimeForStore(model['repeatUntilDate'], timezone)
      }
      return model
    },
    excludeDates: (model, { timezone }) => {
      model['excludeDates'] = model['excludeDates'].map(excludeDate => {
        return this.transformDatetimeForStore(excludeDate, timezone)
      })
      return model
    }
  }
}