// @flow

/**
 * Interface for Dom class
 * @memberOf Dom
 */
export interface DomInterface {
  getElements(selector: string): Array<any>;
  getElement(selector: string): any;
}
