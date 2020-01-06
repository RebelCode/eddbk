// @flow
import type Vue from 'vue-flow-definitions/definitions/vue_v2.x.x/vue_v2.x.x'

/**
 * Interface for App class
 * @memberOf Core
 */
export interface AppInterface {
  init(): {[string]: Array<Vue>};
}
