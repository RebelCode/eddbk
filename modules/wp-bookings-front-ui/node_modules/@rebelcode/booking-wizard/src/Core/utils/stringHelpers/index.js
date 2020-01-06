/**
 * String helping utils.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies Dependencies list.
 *
 * @return {object}
 */
export default function (dependencies) {
  return {
    'markdownReplace' (container) {
      const rules = {
        '\\*(.*?)\\\*': (match, text) => {
          return `<strong>${text}</strong>`
        }
      }
      return str => {
        for (let regexp of Object.keys(rules)) {
          const expression = new RegExp(regexp, '\g')
          str = str.replace(expression, rules[regexp])
        }
        return str
      }
    },
    'kebabToCamelCase' () {
      /**
       * Transform kebab cased string to camel cased string.
       *
       * @since [*next-version*]
       *
       * @param {string} kebabString String in kebab case that should be transformed to camel case.
       *
       * @return {string} Camel cased string.
       */
      return kebabString => kebabString.replace(/-([a-z])/g, g => g[1].toUpperCase())
    },
    'getLabel' (container) {
      /**
       * Get and render localized string from labels list.
       *
       * @since [*next-version*]
       *
       * @param {string} labelKey Label key in application labels list.
       * @param {object?} params Params to use in string.
       *
       * @return {string} Formatted string.
       */
      return (labelKey, params) => {
        const format = container.lodash.get(container.state.applicationLabels, labelKey, labelKey)
        return container.markdownReplace(container.textFormatter(format, params))
      }
    }
  }
}
