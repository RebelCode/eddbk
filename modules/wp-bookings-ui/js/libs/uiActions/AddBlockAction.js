import UiAction from './UiAction'

/**
 * Add block to some div.
 *
 * @since [*next-version*]
 */
export default class AddBlockAction extends UiAction {
  /**
   * AddBlockAction constructor.
   *
   * @since [*next-version*]
   *
   * @param {TemplateRenderFunction} renderTemplate Block template renderer.
   * @param {document} document The DOM document.
   * @param {string} selector Selector for the box in which message will be displayed.
   * @param {string} mode How to add block to div. Can be `append` or `prepend`.
   * @param {object} context Context that will be used to render template.
   */
  constructor (renderTemplate, document, { selector, mode, ...context }) {
    super()

    this.renderTemplate = renderTemplate
    this.document = document
    this.selector = selector
    this.mode = mode
    this.context = context

    this.block = null
  }

  /**
   * @inheritDoc
   *
   * @since [*next-version*]
   */
  act () {
    const parentElement = this.document.querySelector(this.selector)

    if (!parentElement) {
      return
    }

    this.block = this.document.createElement('div')
    this.block.innerHTML = this.renderTemplate(this.context)

    if (this.mode === 'prepend') {
      parentElement.insertBefore(this.block, parentElement.firstChild)
    }
    else {
      parentElement.appendChild(this.block)
    }
  }

  /**
   * @inheritDoc
   *
   * @since [*next-version*]
   */
  revert () {
    if (this.block) {
      this.block.remove()
      this.block = null
    }
  }
}
