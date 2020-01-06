import UiAction from './UiAction'

/**
 * Set checkbox's value clicking on it.
 *
 * @since [*next-version*]
 */
export default class CheckboxClickAction extends UiAction {
  /**
   * CheckboxClickAction constructor.
   *
   * @since [*next-version*]
   *
   * @param {jQuery} jQuery jQuery object.
   * @param {boolean} value Target value of checkbox.
   * @param {string} selector Checkbox's selector.
   */
  constructor (jQuery, { value, selector }) {
    super()

    this.value = value
    this.selector = selector

    this.jQuery = jQuery

    this.initialValue = null
  }

  /**
   * @inheritDoc
   *
   * @since [*next-version*]
   */
  act () {
    this.initialValue = this._getCheckboxValue()

    if (this.initialValue !== this.value) {
      this._setCheckboxValue(this.value)
    }
  }

  /**
   * @inheritDoc
   *
   * @since [*next-version*]
   */
  revert () {
    this._setCheckboxValue(this.initialValue)
  }

  /**
   * Get value of the checkbox.
   *
   * @since [*next-version*]
   *
   * @return {boolean} The checkbox's value.
   */
  _getCheckboxValue () {
    return this.jQuery(this.selector).is(':checked')
  }

  /**
   * Set checkbox's new value.
   *
   * @since [*next-version*]
   *
   * @param {boolean} value New checkbox's value.
   */
  _setCheckboxValue (value) {
    const currentValue = this._getCheckboxValue()
    if (value === currentValue) {
      return
    }
    this._clickOnCheckbox()
  }

  /**
   * Clicks on checkbox using jQuery.
   *
   * @since [*next-version*]
   */
  _clickOnCheckbox () {
    this.jQuery(this.selector).click()
  }
}
