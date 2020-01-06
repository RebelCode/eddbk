/**
 * Toggleable class for encapsulate "on/off" switching
 * logic. For example, for toggling pages visibility.
 *
 * @since [*next-version*]
 */
export class FunctionalToggleable {
  constructor(set, get) {
    this._stateSetter = set
    this._stateGetter = get
  }

  /**
   * Set On state.
   *
   * @since [*next-version*]
   *
   * @param isOn
   * @private
   */
  _setState(isOn) {
    this._stateSetter(!!isOn)
  }

  /**
   * Get On state.
   *
   * @since [*next-version*]
   *
   * @returns {*}
   * @private
   */
  _getState() {
    return this._stateGetter()
  }

  /**
   * Get On state.
   *
   * @since [*next-version*]
   *
   * @returns {boolean}
   */
  isOn() {
    let state = this._getState()
    return !!state
  }

  /**
   * Set current on state.
   *
   * @since [*next-version*]
   *
   * @param isOn
   */
  setState(isOn) {
    this._setState(!!isOn)
  }
}
