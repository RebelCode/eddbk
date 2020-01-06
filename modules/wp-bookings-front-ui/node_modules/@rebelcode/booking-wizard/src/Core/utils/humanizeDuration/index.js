import nonPluralEnglish from './languages/nonPluralEnglish'

/**
 * Humanize duration utils.
 *
 * @since [*next-version*]
 *
 * @param {object} dependencies Dependencies list.
 *
 * @return {object}
 */
export default function (dependencies) {
  const HumanizeDuration = dependencies.humanizeDuration
  return {
    /**
     * Humanize duration factory function.
     *
     * @since [*next-version*]
     *
     * @return {Function}
     */
    humanizeDurationFactory () {
      return (options = {}) => {
        const defaults = {
          units: ['w', 'd', 'h', 'm'],
          round: true
        }
        return HumanizeDuration.humanizer(Object.assign({}, defaults, options))
      }
    },

    /**
     * Non plural humanizer version.
     *
     * @since [*next-version*]
     *
     * @param {{humanizeDurationFactory: Function}} container Container.
     *
     * @type {HumanizeDuration}
     */
    nonPluralHumanizeDuration (container) {
      return container.humanizeDurationFactory({
        language: 'nonPluralEnglish',
        languages: {
          nonPluralEnglish
        }
      })
    },

    /**
     * Default humanizer.
     *
     * @since [*next-version*]
     *
     * @param {{humanizeDurationFactory: Function}} container Container.
     *
     * @type {HumanizeDuration}
     */
    humanizeDuration (container) {
      return container.humanizeDurationFactory()
    }
  }
}
