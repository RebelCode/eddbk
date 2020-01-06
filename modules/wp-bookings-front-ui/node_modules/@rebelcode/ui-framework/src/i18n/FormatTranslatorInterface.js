// @flow

/**
 * @memberOf I18n
 */
export interface FormatTranslatorInterface {
  translate(format: string, params?: Array<mixed>, context?: string): string;
}
