export default {
  /**
   * Seed service editor by passed model.
   *
   * @since [*next-version*]
   *
   * @param state
   * @param model
   */
  setServiceEditorState (state, model) {
    state.one = model
  },

  /**
   * Seed session editor by passed model.
   *
   * @since [*next-version*]
   *
   * @param state
   * @param model
   */
  setSessionEditorState (state, model) {
    state.oneSession = model
  },

  /**
   * Set loading state value.
   *
   * @since [*next-version*]
   *
   * @param state
   * @param newLoadingState
   */
  setLoadingList (state, newLoadingState) {
    state.isLoadingList = newLoadingState
  }
}
