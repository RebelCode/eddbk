/**
 * Component for switching between days.
 *
 * @since [*next-version*]
 *
 * @param {CreateDatetimeCapable} CreateDatetimeCapable Mixin that provides ability to work with datetime.
 * @param {{
 *  sessionTime: (string), // How to format session time,
 *  dayFull: (string), // How to format day for day heading,
 *  dayShort: (string), // How to format day for sessions,
 * }} dateFormats Map of date formats for formatting dates in UI.
 *
 * @return {object}
 */
export default function (CreateDatetimeCapable, dateFormats) {
  return {
    template: '#date-navigator-template',

    mixins: [ CreateDatetimeCapable ],

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {String|null} selectedDay Selected day to show sessions from.
       */
      selectedDay: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {string|null} timezone Name of timezone in which sessions will be displayed.
       */
      timezone: {
        default: null
      },

      /**
       * The previous closest available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null}
       */
      prevAvailableDay: {
        default: null
      },

      /**
       * The next closest available day with sessions.
       *
       * @since [*next-version*]
       *
       * @property {string|null}
       */
      nextAvailableDay: {
        default: null
      },
    },
    computed: {
      /**
       * Formatted day label for session selection row.
       *
       * @since [*next-version*]
       *
       * @property {string}
       */
      selectedDaySessionsLabel () {
        return this.createDatetime(this.selectedDay).format(dateFormats.dayShort)
      },
    },
    methods: {
      /**
       * Navigate to the nearest previous day with available sessions.
       *
       * @since [*next-version*]
       */
      goToPrevDay () {
        this.$emit('update:selectedDay', this.prevAvailableDay)
      },

      /**
       * Navigate to the nearest next day with available sessions.
       *
       * @since [*next-version*]
       */
      goToNextDay () {
        this.$emit('update:selectedDay', this.nextAvailableDay)
      }
    }
  }
}