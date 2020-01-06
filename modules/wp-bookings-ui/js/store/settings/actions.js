export default {
  /**
   * Update settings.
   *
   * @param {Function} commit Function for committing to store.
   * @param {UpdateCapable} api Update capable API.
   * @param {object} data Data that should be sent.
   *
   * @return {Promise<any>} Update response promise.
   */
  updateSettings ({ commit }, { api, data }) {
    return api.update(data)
  }
}