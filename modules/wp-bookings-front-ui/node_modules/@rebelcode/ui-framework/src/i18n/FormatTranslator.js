// @flow

import {FormatTranslatorInterface} from './FormatTranslatorInterface'

/**
 * Class for text translating
 * @memberOf I18n
 */
export default class FormatTranslator implements FormatTranslatorInterface {
  formatter: Function;

  /**
   * Insert formatter function
   * @param {function} formatter - function for replacing
   */
  constructor (formatter: (format: string, params: ?Array<mixed>) => string) {
    this.formatter = formatter
  }

  /**
   * Translate string to needed language
   *
   * @param {string} format  - initial string for translating
   * @param {array}  params  - params for replacing
   * @param {string} context - current context
   *
   * @returns {string} translated string
   */
  translate (format: string, params: ?Array<mixed>, context: ?string) {
    return this.formatter(format, params)
  }
}
