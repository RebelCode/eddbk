/**
 * Object that is able to make some changes to UI
 * and revert that changes.
 *
 * @since [*next-version*]
 */
export default class UiAction {
  /**
   * Perform this action.
   *
   * @since [*next-version*]
   */
  act () {
    throw new Error('Implement `act()` method.')
  }

  /**
   * Revert result of doing this action.
   *
   * @since [*next-version*]
   */
  revert () {
    throw new Error('Implement `revert()` method.')
  }
}