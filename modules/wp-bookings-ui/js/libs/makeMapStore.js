/**
 * `mapStore` factory function.
 *
 * @since [*next-version*]
 *
 * @param {Function} deepGet Function that cen get values from nested objects.
 *
 * @return {mapStoreFunction}
 */
export default function (deepGet) {
  /**
   * Helper function for mapping Vuex state and getters with API similar
   * to Vuex's map*Something* helpers.
   *
   * It will create computed properties with inner `get()` and `set(value)` methods
   * for items in state and for getters. For getters there is only `get()` available,
   * but for items in state `set(value)` is also available which allows us to set state item`s
   * value in code using `=`:
   * ```js
   * this.fooInStore = 'bar'
   * ```
   *
   * For example, you have store module in namespace `basket` with two items
   * in state: `selectedApple`, `list` and one getter `greenApplesList`.
   *
   * Using this helper map function you can simply write:
   * ```js
   * computed: {
   *   ...mapStore('basket', [
   *     'selectedApple',
   *     'list',
   *     'greenApplesList'
   *   ])
   * }
   * ```
   * and then in your code you can write:
   * ```js
   * this.selectedApple = this.greenApplesList[1]
   * ```
   *
   * Under the hood it will execute `set` mutation on bucket's store
   * ans set greenApplesList[1] as `selectedApple's` value.
   *
   * @since [*next-version*]
   *
   * @param namespace {string} Store module's namespace name
   * @param map {array|object} State or getter keys in store to map.
   *                           Use object if you want to use different name in consuming code:
   *                           `{'preferredName': 'realNameInStore'}`
   * @return {*}
   */
  return (namespace, map) => {
    if (Array.isArray(map)) {
      const _map = {}
      map.forEach((key) => { _map[key] = key })
      map = _map
    }
    Object.keys(map).forEach((key) => {
      const storeKey = map[key]
      map[key] = {
        get () {
          if (this.$store.getters.hasOwnProperty(namespace + '/' + storeKey)) {
            return this.$store.getters[namespace + '/' + storeKey]
          }
          return deepGet(this.$store.state, namespace + '.' + storeKey)
        },
        set (value) {
          if (this.$store.getters.hasOwnProperty(namespace + '/' + storeKey)) {
            throw new Error('Cannot execute setter on value from getter')
          }
          this.$store.commit('set', {key: namespace + '.' + storeKey, value})
        }
      }
    })
    return map
  }
}
