export default function CfBookingEditor (AbstractEntityModalEditor, {mapState, mapMutations, mapActions}, moment, debounce) {
  return AbstractEntityModalEditor.extend({
    inject: {
      'clientsApi': 'clientsApi',
      'bookingsApi': 'bookingsApi',

      /**
       * API Errors Handler factory function.
       *
       * @since [*next-version*]
       *
       * @var {Function}
       */
      'apiErrorHandlerFactory': 'apiErrorHandlerFactory',

      'config': 'config',
      'state': 'state',

      'bookingStoreTransformer': 'bookingStoreTransformer',

      'momentHelpers': 'momentHelpers',
      'helpers': {
        from: 'bookingHelpers'
      },

      /**
       * Function for i18n strings
       *
       * @since [*next-version*]
       *
       * @var {Function} _
       */
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

      'humanizeDuration': 'humanizeDuration',

      /**
       * Modal state injected from elsewhere.
       *
       * @var {FunctionalToggleable}
       */
      modalState: {
        from: 'bookingEditorState'
      },

      /**
       * Editing entity items collection. Used to remove editing
       * items from it when user confirm deletion.
       *
       * @var {FunctionalArrayCollection}
       */
      entitiesCollection: {
        from: 'bookingsCollection'
      },

      modal: 'modal',
      repeater: 'repeater',

      datepicker: 'datepicker',
      vueselect: 'vueselect',

      'datetime-picker': 'datetime-picker',
      'timezone-select': 'timezone-select',
      'service-session-selector': 'service-session-selector',
    },

    data () {
      return {
        isCreateConfirming: false,

        isBookingSaving: false,

        isClientsLoading: false,
        isCreatingClient: false,
        isSavingClient: false,

        isDeleting: false,

        model: {
          id: null,

          client: null,
          service: null,
          session: null,

          status: null,

          payment: null,

          clientTzName: 'UTC+0',
          notes: null,

          transition: '',
        },

        /**
         * Transition name for applying when booking is created.
         *
         * @property {string}
         */
        createTransition: false,

        /**
         * @since [*next-version*]
         *
         * @property {string|null} errorMessage Booking's API response error message
         */
        errorMessage: null,

        /**
         * @since [*next-version*]
         *
         * @property {string|null} clientErrorMessage Client's API response error message
         */
        clientErrorMessage: null,

        newClient: {
          name: '',
          email: ''
        },

        /**
         * @property {object[]} List of clients found by search for autocomplete.
         */
        foundClients: [],

        /**
         * @since [*next-version*]
         *
         * @property {ApiErrorHandler} bookingApiHandler Handles error responses for bookings.
         */
        bookingApiErrorHandler: this.apiErrorHandlerFactory((error) => {
          this.isBookingSaving = false
          this.isDeleting = false
          this.errorMessage = error
        }),

        /**
         * @since [*next-version*]
         *
         * @property {ApiErrorHandler} clientApiHandler Handles error responses for clients.
         */
        clientApiErrorHandler: this.apiErrorHandlerFactory((error) => {
          this.isClientsLoading = false
          this.isSavingClient = false
          this.clientErrorMessage = error
        })
      }
    },

    watch: {
      model: {
        deep: true,
        handler () {
          this.errorMessage = null
          /*
           * Add self-transition when modal for editing is opened.
           */
          if (this.selfTransition && !this.model.transition) {
            this.mountedModel.transition = this.model.transition = this.selfTransition
          }
        }
      },
      newClient: {
        deep: true,
        handler () {
          this.clientErrorMessage = null
        }
      }
    },

    /**
     * Created component hook. 
     * Populates client timezone select with default website timezone.
     * 
     * @since [*next-version*]
     */
    created () {
      this.seedLock = true
      if (this.config.timezone) {
        this.model.clientTzName = this.config.timezone
      }
      this.seedLock = false
    },

    computed: {
      ...mapState('bookings', {
        entityModel: state => state.bookingModel,
        services: state => state.services,
        timezone: state => state.timezone,
      }),

      /**
       * CSS rules for styling booking status "pill" in editor.
       *
       * @return {object}
       */
      statusStyle () {
        return this.helpers.statusStyle(this.model.status)
      },

      /**
       * Transition key that will keep booking status.
       *
       * @since [*next-version*]
       *
       * @return {string | undefined}
       */
      selfTransition () {
        const bookingStatus = this.model.status
        if (!bookingStatus) {
          return
        }

        const transitions = this.state.statusTransitions[bookingStatus]

        return Object.keys(transitions)
          .find(transition => transitions[transition] === bookingStatus)
      },

      /**
       * Get map of available transitions for current booking.
       *
       * @since [*next-version*]
       *
       * @return {object} Map of transition key to labels.
       */
      availableTransitions () {
        const bookingStatus = this.model.status
        const transitions = this.state.statusTransitions[bookingStatus]
        const hiddenTransitions = this.state.hiddenStatusesTransitions[bookingStatus] ||  this.state.hiddenStatusesTransitions['all']

        /*
         * Remove hidden transitions
         */
        let allowedTransitions = Object.keys(transitions)
          .filter(transition => hiddenTransitions.indexOf(transition) === -1)
          .filter(transition => transition !== this.selfTransition)
        allowedTransitions.unshift(this.selfTransition)

        let transitionsMap = allowedTransitions.reduce((res, key) => (res[key] = this.state.statusesTransitionsLabels['all'][key], res), {})

        /*
         * Apply status-specific labels, if there are some
         */
        if (this.state.statusesTransitionsLabels[bookingStatus]) {
          for (let transitionKey in this.state.statusesTransitionsLabels[bookingStatus]) {
            transitionsMap[transitionKey] = this.state.statusesTransitionsLabels[bookingStatus][transitionKey]
          }
        }
        transitionsMap[this.selfTransition] = this._("Don't change booking status")

        return transitionsMap
      },

      /**
       * Get human readable duration of booking with minutes
       * precision:
       *
       * 4 weeks, 4 days, 7 hours and 55 minutes.
       *
       * @return {string|null}
       */
      duration () {
        if (!this.model.start || !this.model.end) return null

        const diffInMilliseconds = Math.abs(moment(this.model.end).diff(moment(this.model.start), 'minutes')) * 60 * 1000

        if (!diffInMilliseconds) {
          return null
        }

        return this.humanizeDuration(diffInMilliseconds, {
          conjunction: ' and ',
          serialComma: false,
          units: ['y', 'w', 'd', 'h', 'm'],
          round: true
        })
      },

      /**
       * Booking status title for displaying.
       *
       * @return {string|null}
       */
      statusTitle () {
        if (!this.model.status) {
          return null
        }
        return this.helpers.statusLabel(this.model.status)
      }
    },

    methods: {
      ...mapActions('bookings', [
        'saveBookingOnBackend',
      ]),

      /**
       * Close all confirmation dialogs.
       */
      closeAllConfirmation () {
        /**
         * Set default confirmation state
         *
         * @type {boolean}
         */
        this.removeConfirming = false
        this.closeConfirming = false
        this.cancelConfirming = false

        this.isCreateConfirming = false
        this.isCreatingClient = false

        this.createTransition = false
      },

      /**
       * Save *new* booking with given status. It will show
       * confirmation dialog with asking user to confirm saving
       * booking with status.
       *
       * @param {string|boolean} createTransition Status for newly created booking.
       */
      saveNewBooking (createTransition = false) {
        this.createTransition = createTransition
        this.isCreateConfirming = true
      },

      /**
       * Confirm booking creation and close modal.
       */
      confirmBookingCreation () {
        this.$validator.validateAll().then((result) => {
          if (!result) {
            this.isCreateConfirming = false
            return;
          }
          this.saveBooking().then(() => {
            this.isCreateConfirming = false
          })
        })
      },

      /**
       * Save booking.
       */
      saveBooking () {
        let model = this.bookingStoreTransformer.transform(Object.assign({}, this.model))
        // @todo: fix this
        if (!model.id) {
          model['transition'] = 'draft'
        }

        this.isBookingSaving = true

        return this.saveBookingOnBackend({api: this.bookingsApi, model}).then((response) => {
          return response
        }).then((response) => {
          if (!this.model.id && this.createTransition) {
            return this.applyTransition(response, this.createTransition)
          }
          return response
        }).then(() => {
          this.$emit('updated')
          this.forceCloseModal()
          this.isBookingSaving = false
        }).catch(error => this.bookingApiErrorHandler.handle(error))
      },

      /**
       * Apply this transition when new booking is created.
       *
       * @param {object} response
       * @param {string} transition Transition to apply.
       * @return {*}
       */
      applyTransition (response, transition) {
        const model = {
          id: response.data.id,
          transition
        }
        return this.saveBookingOnBackend({
          api: this.bookingsApi, model
        })
      },

      confirmBookingDeletion () {
        this.isDeleting = false
        this.removeConfirming = true
      },

      deleteBooking () {
        this.isDeleting = true
        this.$emit('delete', Object.assign({}, this.model))
      },

      /**
       * Format time in given offset for displaying universal
       * and client's times.
       *
       * @param {any} time Time in any format moment can accept.
       * @param {string} timezone Timezone name
       * 
       * @return {string} Formatted string
       */
      format (time, timezone = 'UTC+0') {
        if (!time) return

        const serviceTimezone = !!this.model.service ? this.model.service.timezone : this.config.timezone
        const timeInServiceTimezone = this.momentHelpers.createInTimezone(time, serviceTimezone)

        return this.momentHelpers.switchToTimezone(timeInServiceTimezone.format(), timezone).format('DD/MM/YY HH:mm')
      },

      /**
       * Search clients on the server.
       *
       * @param {String} search      Current search text
       * @param {Function} loading   Toggle loading class
       */
      onClientSearch (search, loading) {
        this.isClientsLoading = true
        loading(true)
        this._searchClients(loading, search, this);
      },

      _searchClients: debounce((loading, search, vm) => {
        vm.clientsApi.fetch({search}).then((response) => {
          vm.foundClients = response.data.items
          vm.isClientsLoading = false
          loading(false)
        })
      }, 350),

      /**
       * Show client creation form.
       */
      createClient () {
        this.newClient = {
          name: '',
          email: ''
        }
        this.isCreatingClient = true
      },

      /**
       * Start value is changed
       */
      startChanged () {
        this.$nextTick(() => {
          if (this.model.end) {
            this.$validator.validate('end')
          }
        })
        this.errors.remove('start')
      },

      /**
       * Close client creation form.
       */
      cancelClientCreation () {
        this.errors.clear('client-editor')
        this.isCreatingClient = false
      },

      /**
       * Store new client on the backend.
       */
      saveNewClient () {
        this.$validator.validateAll('client-editor').then((result) => {
          if (!result) {
            return
          }
          this.isSavingClient = true
          this.clientsApi.create(this.newClient).then((response) => {
            this.model.client = response.data
            this.isSavingClient = false
            this.isCreatingClient = false
          }).catch(error => this.clientApiErrorHandler.handle(error))
        })

      }
    },

    components: {
      modal: 'modal',
      vueselect: 'vueselect',
      'datetime-picker': 'datetime-picker',
      'timezone-select': 'timezone-select',
      'service-session-selector': 'service-session-selector'
    }
  })
}