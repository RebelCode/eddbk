export default {
  /**
   * Whether the list of staff members is loading.
   *
   * @since [*next-version*]
   *
   * @property {boolean} isLoadingList
   */
  isLoadingList: false,

  /**
   * The list of staff members on the screen.
   *
   * @since [*next-version*]
   *
   * @property {object[]} list
   */
  list: [],

  /**
   * Whether the staff member's editor is visible.
   *
   * @since [*next-version*]
   *
   * @property {boolean} isModalVisible
   */
  isModalVisible: false,

  /**
   * The staff member that is being edited.
   *
   * @since [*next-version*]
   *
   * @property {object} one
   */
  one: {
    id: null,
    name: '',
    data: {
      imageId: null,
      imageUrl: null,
    },
    availability: {
      rules: [],
      timezone: null
    }
  },
}
