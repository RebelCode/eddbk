import { Components } from './components'
import { Mixins } from './mixins'
import { Store } from './store'
import { Utils } from './utils'

export default function (dependencies) {
  return {
    ...Components.makeServices(dependencies),
    ...Mixins.makeServices(dependencies),
    ...Store.makeServices(dependencies),
    ...Utils.makeServices(dependencies),

    /**
     * The VueJS constructor.
     *
     * @since [*next-version*]
     *
     * @return {Vue}
     */
    vue () {
      const Vue = dependencies.vue
      Vue.use(dependencies.uiFramework.Core.InjectedComponents)
      return Vue
    },

    /**
     * Vuex.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Vuex}
     */
    vuex (container) {
      let Vue = container.vue,
        Vuex = dependencies.vuex
      Vue.use(Vuex)
      return Vuex
    },

    /**
     * Http client.
     *
     * @since [*next-version*]
     *
     * @return {HttpClient}
     */
    httpClient () {
      return dependencies.axios
    },

    /**
     * Instance of authorized HTTP client.
     *
     * @since [*next-version*]
     *
     * @return {HttpClient}
     */
    authorizedHttpClient (container) {
      return container.httpClient.create({
        headers: {
          'X-EDDBK-Nonce': container.state.wpRestNonce
        }
      })
    },

    /**
     * Moment JS.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Moment}
     */
    moment (container) {
      const moment = dependencies.moment
      return dependencies.momentRange.extendMoment(moment)
    },

    /**
     * Humanize duration library.
     *
     * @since [*next-version*]
     *
     * @return {humanizeDuration}
     */
    humanizeDuration () {
      return dependencies.humanizeDuration
    },

    /**
     * Transformer for reading sessions.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {SessionReadTransformer}
     */
    sessionReadTransformer(container) {
      return new dependencies.bookingWizardComponents.SessionReadTransformer(container.moment)
    },

    /**
     * Hash function implementation.
     *
     * @since [*next-version*]
     *
     * @return {Function}
     */
    hashFunction () {
      return dependencies.sha1
    },

    /**
     * Request cache.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {RequestCache}
     */
    requestCache (container) {
      return new dependencies.stdLib.RequestCache(container.hashFunction)
    },

    /**
     * Range cache.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {RangeCache}
     */
    rangeCache (container) {
      return new dependencies.bookingWizardComponents.RangeCache(container.moment, container.differenceWith, container.isEqual)
    },

    /**
     * Sessions API.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {SessionApi}
     */
    sessionApi (container) {
      return new dependencies.bookingWizardComponents.SessionApi(
        container.httpClient,
        container.config.endpoints.sessions,
        container.requestCache,
        container.rangeCache,
        container.sessionReadTransformer,
        container.moment
      )
    },

    /**
     * Function for finding difference between two arrays.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Function}
     */
    differenceWith (container) {
      return container.lodash.differenceWith
    },

    /**
     * Function for checking that object are equal.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Function}
     */
    isEqual (container) {
      return container.lodash.isEqual
    },

    /**
     * Lodash library.
     *
     * @since [*next-version*]
     *
     * @return {lodash}
     */
    lodash () {
      return dependencies.lodash.noConflict()
    },

    /**
     * Text formatter.
     *
     * @since [*next-version*]
     *
     * @return {Function}
     */
    textFormatter () {
      return dependencies.textFormatter.vsprintf
    },

    /**
     * Format translator.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {FormatTranslator}
     */
    translator (container) {
      return new dependencies.uiFramework.I18n.FormatTranslator(
        container.textFormatter
      )
    },

    /**
     * Function for translating strings with params.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Function}
     */
    translate (container) {
      return function (format, params) {
        return container.translator.translate(format, params)
      }
    },
  }
}
