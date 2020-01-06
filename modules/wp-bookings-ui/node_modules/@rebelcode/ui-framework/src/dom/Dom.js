// @flow
import {DomInterface} from './DomInteface'

/**
 * Abstraction for DOM manager
 * @memberOf Dom
 */
export default class Dom implements DomInterface {
  document = {};

  /**
   * Insert document object
   * @param {Document} document - DOM document object
   */
  constructor (document: Document) {
    if (typeof document === 'undefined') {
      throw new Error('The document object does not exist!')
    }
    this.document = document
  }

  /**
   * Get elements list from DOM
   * @param {string} selector - element selector
   * @returns {array} list of DOM nodes
   */
  getElements (selector: string) {
    const elements = this.document.querySelectorAll(selector)
    if (elements && elements.length > 0) {
      return [].slice.call(elements)
    }
    return []
  }

  /**
   * Get DOM element
   * @param {string} selector - element selector
   * @returns {Element}
   */
  getElement (selector: string) {
    return this.document.querySelector(selector)
  }
}
