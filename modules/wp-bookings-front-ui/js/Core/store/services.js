import makeStore from './store'

export default function (dependencies) {
  return {
    /**
     * Vuex store of application.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {Vuex.Store}
     */
    store (container) {
      const store = makeStore(container.resolvedVuexModules, {
        deepHas: container.lodash.has,
        deepSet: container.lodash.set
      })
      return new container.vuex.Store(store)
    },

    /**
     * Service for resolving vuex store modules.
     *
     * @since [*next-version*]
     *
     * @param {Container} container DI Container.
     *
     * @return {object} List of vuex modules.
     */
    resolvedVuexModules (container) {
      let modules = {}
      container.vuexModules.$list().forEach(moduleKey => {
        modules[moduleKey] = container[container.vuexModules[moduleKey]]
      })
      return modules
    },
  }
}