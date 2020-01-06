import GetResourceCapable from '../modules/services/mixins/GetResourceCapable'

export function CfBookingsListView (AbstractBookingsView, { mapState }, moment) {
  return AbstractBookingsView.extend({
    mixins: [ GetResourceCapable ],

    inject: {
      'isMobile': 'isMobile',
      'bookingHelpers': 'bookingHelpers',
      'config': 'config',
      'list-table': {
        from: 'wpListTable'
      },
      '_': {
        from: 'translate'
      },

      /**
       * Function for text formatting, it will replace placeholders to given values.
       *
       * @var {Function}
       */
      'formatter': {
        from: 'textFormatter'
      },

      /**
       * Function for creating datetime in timezone.
       *
       * @since [*next-version*]
       *
       * @property {CreateDatetimeFunction} createDatetime
       */
      'createDatetime': 'createDatetime'
    },

    data () {
      return {
        page: 1,
        month: null,

        /**
         * @since [*next-version*]
         *
         * @property {string[]} Array of component's fields that will be used for bookings search.
         */
        requestParams: [
          'page', 'month', 'service', 'search', 'status', 'numItems'
        ],

        actions: [
          {
            key: 'edit',
            label: this._('Edit / View Booking')
          },
          {
            key: 'trash',
            label: this._('Delete Permanently')
          }
        ],

        columns: {
          'date': {
            label: this._(this.isMobile() ? 'Booking details' : 'Booking date and time'),
          },
          'client': {
            label: this._('Client Name')
          },
          'service': {
            label: this._('Service')
          },
          'status': {
            label: this._('Booking Status')
          }
        },
      }
    },

    props: {
      /**
       * Number of items per one page.
       *
       * @since [*next-version*]
       *
       * @var {number} numItems
       */
      numItems: {
        type: Number,
        default: 20
      }
    },

    computed: {
      pagesCount () {
        if (!this.bookingsCount) {
          return 1
        }
        return Math.ceil(this.bookingsCount / this.numItems)
      },
      ...mapState('bookings', [
        'timezone'
      ])
    },

    methods: {
      humanizeDate (date) {
        return this.createDatetime(date, this.timezone).format('h:mm a, dddd, Do MMMM YYYY')
      },

      onActionClick (action, row) {
        if (action === 'edit') {
          this.$emit('edit', row)
        }
        if (action === 'trash') {
          this.$emit('delete', row, true)
        }
      },

      goToPage (page) {
        this.page = page
        this.applyFilter()
      },

      _month(monthNumber) {
        return moment(monthNumber, 'M').format('MMMM')
      }
    },

    components: {
      'list-table' : 'list-table',
    }
  })
}
