/**
 * Component for selecting booking sessions time with needed duration.
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
    template: '#session-time-picker-template',

    mixins: [ CreateDatetimeCapable ],

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {object|null} value Selected session, `v-model` in parent
       */
      value: {},

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
       * @property {object[]} Array of sessions for current day.
       */
      sessions: {
        default () {
          return []
        }
      },

      /**
       * @since [*next-version*]
       *
       * @property {string|null} timezone Name of timezone in which sessions will be displayed.
       */
      timezone: {
        default: null
      },
    },

    computed: {
      /**
       * Sessions that matches selected duration
       *
       * @since [*next-version*]
       *
       * @return {object[]}
       */
      visibleSessions () {
        return this.sessions.sort((a, b) => (a.startUnix - b.startUnix))
      },
    },

    methods: {
      /**
       * Session label for representing session in UI.
       *
       * @since [*next-version*]
       *
       * @param {object} session Session object to get label for.
       *
       * @return {*}
       */
      sessionLabel (session) {
        return this.createLocalDatetime(session.start).format(dateFormats.sessionTime)
      },

      /**
       * Check that session is currently selected session.
       *
       * @since [*next-version*]
       *
       * @param {object} session Session to check.
       *
       * @return {boolean}
       */
      isSelected (session) {
        return this.value
          && this.value.start === session.start
          && this.value.end === session.end
      },

      /**
       * Select session.
       *
       * @since [*next-version*]
       *
       * @param {object} session
       */
      select (session) {
        this.$emit('input', session)
      }
    }
  }
}
  