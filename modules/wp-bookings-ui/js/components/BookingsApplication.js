export function CfBookingsApplication(state, store, { mapState, mapMutations, mapActions, mapGetters }, Vue, FunctionalArrayCollection) {
  return {
    store,
    inject: {
      'selection-list': 'selection-list',
      'bookings-calendar-view': 'bookings-calendar-view',
      'bookings-list-view': 'bookings-list-view',
      'booking-editor': 'booking-editor',
      'tabs': 'tabs',
      'tab': 'tab',
      'timezone-select': 'timezone-select',

      'httpClient': 'httpClient',

      '_': {
        from: 'translate'
      },

      'api': {
        from: 'bookingsApi'
      },
      /**
       * Modal state injected from elsewhere.
       *
       * @var {FunctionalToggleable}
       */
      modalState: {
        from: 'bookingEditorState'
      },

      /**
       * API Errors Handler factory function.
       *
       * @since [*next-version*]
       *
       * @var {Function}
       */
      'apiErrorHandlerFactory': 'apiErrorHandlerFactory',

      /**
       * Notifications center for displaying notifications in UI.
       *
       * @since [*next-version*]
       *
       * @var {NotificationsCenter}
       */
      'notificationsCenter': 'notificationsCenter'
    },
    data () {
      return {
        activeView: 0,

        tabsConfig: {
          switcherItemClass: 'fc-button fc-state-default tab-item',
          switcherActiveItemClass: 'fc-state-active',
          switcherClass: 'fc-button-group fc-button-group--padded',
          tabsClass: 'tabs-content--no-paddings'
        },
        
        selectedStatuses: [],

        timezone: null,

        isStatusesSaving: false,

        statusesCollection: new FunctionalArrayCollection(() => {
          return this.statuses
        }, (newValue) => {}, (item) => {
          return item.key
        }),

        /**
         * @since [*next-version*]
         *
         * @property {ApiErrorHandler} bookingApiHandler Handles error responses for bookings.
         */
        bookingApiErrorHandler: this.apiErrorHandlerFactory((error) => {
          this.setBookingsIsLoading(false)
          this.notificationsCenter.error(error)
        }),
      }
    },
    computed: {
      ...mapState({
        statuses (state) {
          return Object.keys(state.app.statuses).map(key => {
            return {
              key,
              value: state.app.statuses[key]
            }
          })
        },
        screenStatuses: state => state.app.screenStatuses,
        screenOptionsEndpoint: state => state.app.screenOptionsEndpoint,
      }),
      ...mapState('ui', {
        isLoading: state => state.bookings.isLoading,
        viewFilter: state => state.bookings.viewFilter,
      }),
      ...mapGetters('bookings', [
        'getLastRequestParameters'
      ])
    },
    created () {
      if (!state) {
        throw new Error('App state not initialized')
      }

      /*
       * @todo: seed store outside of mounted!
       */
      if (state.services) {
        const services = state.services.slice()
        delete state.services

        this.setInitialState(state)
        this.setServices(services)
      }

      this.selectedStatuses = this.screenStatuses.slice()
      this.timezone = state.bookingsTimezone || state.config.timezone
    },
    methods: {
      ...mapMutations([
        'setInitialState'
      ]),

      ...mapMutations('bookings', [
        'setServices',
        'setBookingEditorState',
        'setBookings',
        'setBookingsCount',
        'setLastRequestParameters',
      ]),

      ...mapMutations('ui', [
        'setBookingsIsLoading',
      ]),

      ...mapActions('bookings', [
        'fetchBookings',
        'deleteBookingFromBackend'
      ]),

      clearBookings () {
        this.setBookings([])
        this.setBookingsCount(0)
      },

      saveScreenOptions (statuses, bookingsTimezone) {
        this.isStatusesSaving = true
        this.httpClient.post(this.screenOptionsEndpoint, { statuses, bookingsTimezone })
          .then(() => {
            this.$store.commit('setScreenStatuses', statuses)
            this.$store.commit('bookings/setTimezone', bookingsTimezone)
            this.refresh()
          })
          .finally(() => {
            this.isStatusesSaving = false
          })
      },

      /**
       * Update bookings list using request parameters.
       *
       * @since [*next-version*]
       *
       * @param {object} params Request parameters for updating bookings.
       */
      update (params) {
        this.setLastRequestParameters(params)
        params = this.normalizeParams(params)
        this.fetch(params)
      },

      /**
       * Refresh bookings list using last request parameters.
       *
       * @since [*next-version*]
       */
      refresh () {
        let params = this.getLastRequestParameters()
        params = this.normalizeParams(params)
        this.fetch(params)
      },

      /**
       * Fetch bookings by search parameters.
       *
       * @since [*next-version*]
       *
       * @return {Promise<any>} Bookings fetch request promise.
       */
      fetch (params = {}) {
        this.setBookingsIsLoading(true)
        return this.fetchBookings({ api: this.api, params }).then(() => {
          this.setBookingsIsLoading(false)
        }).catch(error => this.bookingApiErrorHandler.handle(error))
      },

      /**
       * Prepare params for searching bookings.
       *
       * @since [*next-version*]
       *
       * @param {object} params Bookings filter params.
       *
       * @return {object} Prepared params for booking search.
       */
      normalizeParams (params) {
        params = Object.assign({}, params)
        /*
         * Screen statuses to load
         */
        params['status'] = params['status'] || 'all'
        if (params['status'] === 'all') {
          params['status'] = this.screenStatuses.join(',')
        }
        /*
         * Remove empty search field
         */
        if (!params['search']) {
          delete params['search']
        }
        return params
      },

      createBooking (bookingParams = {}) {
        this._openBookingEditor(bookingParams)
      },

      /**
       * Open booking for editing.
       *
       * @param booking
       */
      editBooking (booking) {
        this._openBookingEditor(booking)
      },

      /**
       * Open booking for editing.
       *
       * @param booking
       */
      deleteBooking (booking, askConfirmation = false) {
        if (askConfirmation) {
          if (confirm(this._('Are you sure you want to delete this booking? There is no undo option.'))) {
            this._deleteBooking(booking)
          }
        }
        else {
          this._deleteBooking(booking)
        }
      },

      _deleteBooking(model) {
        Vue.set(model, 'isDeleting', true)
        this.deleteBookingFromBackend({ api: this.api, model }).then(() => {
          Vue.set(model, 'isDeleting', false)

          this._closeBookingEditor()
          this.refresh()
        })
      },

      _openBookingEditor (booking = {}) {
        this.modalState.setState(true)
        this.setBookingEditorState(booking)
      },

      _closeBookingEditor () {
        this.modalState.setState(false)
        this.setBookingEditorState({})
      },
    },
    components: {
      'booking-editor' : 'booking-editor',
      'bookings-calendar-view' : 'bookings-calendar-view',
      'bookings-list-view' : 'bookings-list-view',
      'tabs': 'tabs',
      'tab': 'tab',
      'timezone-select': 'timezone-select',
      'selection-list': 'selection-list',
    }
  }
}
