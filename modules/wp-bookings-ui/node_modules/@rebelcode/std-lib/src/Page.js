/**
 * Page. Used for handling templates.
 *
 * @since [*next-version*]
 */
export class Page {
  /**
   * Page constructor.
   *
   * @since [*next-version*]
   *
   * @param {string} id Page id.
   * @param {string} template Page template.
   */
  constructor(id, template) {
    this.id = id
    this.template = template
  }

  /**
   * Render page.
   *
   * @since [*next-version*]
   *
   * @param {object} ctx Context for rendering page
   * @return {string} Rendered page
   */
  render(ctx) {
    return this.template
  }
}
