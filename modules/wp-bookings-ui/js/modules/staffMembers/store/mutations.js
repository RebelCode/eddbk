export default {
  /**
   * Seed staff member editor by passed model.
   *
   * @since [*next-version*]
   *
   * @param state
   * @param model
   */
  setStaffMemberEditorState (state, model) {
    state.one = model
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
