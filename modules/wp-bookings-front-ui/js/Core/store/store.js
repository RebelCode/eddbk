/**
 * Main application's store object factory function.
 *
 * @since [*next-version*]
 *
 * @param {object} modules List of vuex modules of app.
 * @param {Function} deepHas Check that object has nested key.
 * @param {Function} deepSet Set value using nested key.
 *
 * @return {object} Store object.
 */
export default function (modules, {deepHas, deepSet}) {
  return {
    modules,
    mutations: {
      /**
       * Deep set value of state.
       *
       * @since [*next-version*]
       *
       * @param {object} state Root store's state.
       * @param {string} key Key to set.
       * @param {*} value Value to set.
       */
      set (state, {key, value}) {
        if (!deepHas(state, key)) {
          throw new Error(`Can't set value. Key "${key}" doesn't exist.`)
        }
        deepSet(state, key, value)
      }
    }
  }
}