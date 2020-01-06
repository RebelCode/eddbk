/**
 * Factory function for mixin that adds ability to use translator
 * inside components.
 *
 * @since [*next-version*]
 *
 * @return {object}
 */
export default function (translate, getLabel) {
  return {
    methods: {
      /**
       * @since [*next-version*]
       *
       * @property {Function} _ Function for translating strings.
       */
      '_': translate,

      /**
       * @since [*next-version*]
       *
       * @property {Function} getLabel Function for getting labels.
       */
      'getLabel': getLabel
    }
  }
}