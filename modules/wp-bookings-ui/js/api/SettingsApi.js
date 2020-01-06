import { Api } from '@rebelcode/std-lib'

/**
 * API for updating settings.
 *
 * @since [*next-version*]
 *
 * @implements {UpdateCapable}
 *
 * @class SettingsApi
 */
export default class SettingsApi extends Api {
  /**
   * Update settings.
   *
   * @since [*next-version*]
   *
   * @param {object} data Data that should be updated.
   *
   * @return {Promise<any>}
   */
  update (data) {
    const { method, url } = this.config['update']
    return this.http.request({
      method,
      url,
      data
    })
  }
}
