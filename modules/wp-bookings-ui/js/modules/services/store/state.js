export default {
  /**
   * Whether the list of services is loading.
   *
   * @since [*next-version*]
   *
   * @property {boolean} isLoadingList
   */
  isLoadingList: false,

  /**
   * The list of services on the screen.
   *
   * @since [*next-version*]
   *
   * @property {object[]} list
   */
  list: [],

  /**
   * Whether the service's editor is visible.
   *
   * @since [*next-version*]
   *
   * @property {boolean} isModalVisible
   */
  isModalVisible: false,

  /**
   * The service that is being edited.
   *
   * @since [*next-version*]
   *
   * @property {object} one
   */
  one: {
    id: null,
    name: null,
    description: null,
    timezone: 'UTC+0',
    availability: {
      rules: []
    },
    sessionTypes: [],
    displayOptions: {
      allowCustomerChangeTimezone: false
    }
  },

  /**
   * The session that is being edited.
   *
   * @since [*next-version*]
   *
   * @property {object} oneSession
   */
  oneSession: {},

  /**
   * Whether the session's editor is visible.
   *
   * @since [*next-version*]
   *
   * @property {boolean} isSessionEditorState
   */
  isSessionEditorState: false
}
