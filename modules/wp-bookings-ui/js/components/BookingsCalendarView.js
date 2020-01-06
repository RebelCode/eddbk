export function CfBookingsCalendarView (AbstractBookingsView, { mapState, mapMutations }, moment) {
  return AbstractBookingsView.extend({
    inject: {
      'bookings-calendar': 'bookings-calendar',
      'switcher': 'switcher',

      'defaultCalendarView': 'defaultCalendarView',

      /**
       * Modal state injected from elsewhere.
       *
       * @var {FunctionalToggleable}
       */
      modalState: {
        from: 'bookingEditorState'
      },
    },

    data () {
      return {
        calendarViews: {
          'agendaDay': 'Day',
          'agendaWeek': 'Week',
          'month': 'Month',
        },

        calendarView: this.defaultCalendarView, // 'month', 'agendaDay'
        colorScheme: 'status', // OR 'service'

        start: null,
        end: null,

        /**
         * @since [*next-version*]
         *
         * @property {string[]} Array of component's fields that will be used for bookings search.
         */
        requestParams: ['start', 'end', 'service', 'search', 'status']
      }
    },

    props: {
      statuses: {
        default () {
          return []
        }
      },
      isLoading: {
        type: Boolean,
        default: false
      }
    },

    watch: {
      calendarView (view) {
        this.$refs.calendar.fireMethod('changeView', view)
      }
    },

    methods: {
      goToToday () {
        this.$refs.calendar.fireMethod('changeView', this.calendarView, moment())
      },

      createBooking (bookingParams) {
        this.$emit('create', bookingParams)
      },

      editBooking (booking) {
        this.$emit('edit', booking)
      },

      /**
       * Update bookings according new dates.
       *
       * @param start {moment} Start date
       * @param end {moment} End date
       */
      updateBookings (start, end) {
        this.start = start.format('YYYY-MM-DD')
        this.end = end.format('YYYY-MM-DD')
        this.applyFilter()
      }
    },

    components: {
      'bookings-calendar' : 'bookings-calendar',
      'switcher' : 'switcher',
    }
  })
}
