export function CfAbstractBookingsView (Vue, { mapState, mapMutations }) {
  return Vue.extend({
    inject: [
      'bookings-filter'
    ],

    props: {
      isLoading: {
        type: Boolean,
        default: false
      }
    },

    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {string[]} Array of component's fields that will be used for bookings search.
         */
        requestParams: []
      }
    },

    computed: {
      ...mapState('bookings', [
        'bookings',
        'bookingsCount',
        'services',
        'filter'
      ]),

      status: {
        get () {
          return this.filter.status
        },
        set (value) {
          this.setBookingsFilter({
            key: 'status',
            value
          })
        }
      },

      search: {
        get () {
          return this.filter.search
        },
        set (value) {
          this.setBookingsFilter({
            key: 'search',
            value
          })
        }
      },

      service: {
        get () {
          return this.filter.service
        },
        set (value) {
          this.setBookingsFilter({
            key: 'service',
            value
          })
        }
      },
    },

    mounted () {
      this.$emit('ready')
      this.$nextTick(() => {
        if (this.isLoading) {
          return
        }
        this.applyFilter()
      })
    },

    methods: {
      ...mapMutations('bookings', [
        'setBookingsFilter'
      ]),

      /**
       * Get search parameters for request.
       *
       * @since [*next-version*]
       *
       * @return {object}
       */
      getRequestParams () {
        return this.requestParams.filter(paramName => {
          return this[paramName] !== null
        }).reduce((res, key) => (res[key] = this[key], res), {})
      },

      /**
       * Apply search filter for bookings application.
       *
       * @since [*next-version*]
       */
      applyFilter () {
        const requestParameters = this.getRequestParams()
        this.$emit('apply-filter', requestParameters)
      },
    },

    components: {
      'bookings-filter': 'bookings-filter',
    }
  })
}