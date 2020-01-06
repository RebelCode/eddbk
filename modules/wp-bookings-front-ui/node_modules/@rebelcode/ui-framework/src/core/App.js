// @flow
import {Dom} from '@/dom'
import {AppInterface} from './AppInterface'
import {ContainerInterface} from '@/container/ContainerInterface'
import {ExportCapableInterface} from '@/container/ExportCapableInterface'
import type Vue from 'vue-flow-definitions/definitions/vue_v2.x.x/vue_v2.x.x'

/**
 * Represents an application that uses the UI framework approach.
 *
 * This application achieves the following:
 * - Automatic creation and configuration of root Vue instances for all selected elements.
 * - Standard set of components for every such instance.
 * - Standard set of services for all Vue root instances and their child compoenents
 * usable with `inject`.
 * - Ability to inject components into child components automatically, making all
 * components usable recursively.
 *
 * This means a single, uniform, automated approach to management of application parts
 * which are distributed in different areas of the page, with a single set of components
 * and other services for all areas.
 *
 * @memberOf Core
 */
export default class App implements AppInterface {
  container = {};

  /**
   * Application constructor.
   *
   * @param {object} container DI container object
   */
  constructor (container: ContainerInterface & ExportCapableInterface) {
    this.container = container
  }

  /**
   * The enter point for the application.
   *
   * @returns {[string]: array}   The registered Vue instances.
   *                              A map, where the key is a selector, and the value is a list of Vue instances.
   */
  init () {
    const sectorList = this.container.get('selectorList')
    const components = this.container.get('components')
    return this._registerVues(sectorList, components)
  }

  /**
   * Create and register a root Vue instance for every DOM element that matches every selector in the list.
   *
   * The result will be a multitude of Vue instances for all elements identified by selectors in the given selector list.
   * Each such instance will have local access to all components from the given component list.
   *
   * @param {array} selectorList  A list of selectors for HTML elements.
   * @param {object} components   Components that will be available to every Vue instance registered for a selector.
   *
   * @returns {[string]: array}   The registered Vue instances.
   *                              A map, where the key is a selector, and the value is a list of Vue instances.
   *
   * @private
   */
  _registerVues (selectorList: Array<string>, components: {[string]: any}) {
    const Vue = this.container.get('vue')
    if (!Vue.isInjectedComponentsInstalled) {
      throw new Error('Injected Components plugin must be installed for UI Framework application')
    }
    const elements = {}
    const domDocument = this.container.get('document')
    const dom = new Dom.Dom(domDocument)
    let Root = Vue.extend()
    selectorList.map((el) => {
      elements[el] = dom.getElements(el).map(this._handleElement.bind(this, Root, components))
    })
    return elements
  }

  /**
   * Creates and configures a Vue instance for a DOM element.
   *
   * Makes sure that all components from the component list are available to that instance,
   * and that each component is set up, if this has not been done already.
   * Root Vue instances will provide standard services to their children.
   * Components may have standard mixins added.
   *
   * @param {Vue} Root          Vue constructor.
   * @param {array} components  Vue component definitions.
   * @param {*} item            DOM element
   *
   * @returns {object}          The new Vue instance, configured for the DOM element.
   *
   * @private
   */
  _handleElement (Root: Vue, components: {[string]: any}, item: any) {
    let instance = new Root({
      components: components,
      provide: this.container.export()
    })
    instance.$mount(item)
    return instance
  }
}
