export default function (store, bookingDataMap, TranslateCapable, MapBookingFieldsCapable) {
  return {
    template: '#eddbk-wizard-template',

    mixins: [ TranslateCapable, MapBookingFieldsCapable ],

    store,

    inject: {
      /**
       * @since [*next-version*]
       *
       * @property {CreateCapable} bookingsApi API wrapper for creating bookings.
       */
      bookingsApi: 'bookingsApi',

      /**
       * @since [*next-version*]
       *
       * @property {object} configuration The configuration of the application.
       */
      configuration: 'config',

      /**
       * @since [*next-version*]
       *
       * @property {Moment} moment Moment JS instance.
       */
      moment: 'moment',

      /**
       * @since [*next-version*]
       *
       * @property {Function} nonPluralHumanizeDuration Function for humanizing durations.
       */
      nonPluralHumanizeDuration: 'nonPluralHumanizeDuration',

      /**
       * @since [*next-version*]
       *
       * @property {Function} kebabToCamelCase Transform kebab cased string to camel cased string.
       */
      kebabToCamelCase: 'kebabToCamelCase',

      /**
       * @since [*next-version*]
       *
       * @property {Function} handleBookSuccess Function for handling successful booking creation response.
       */
      handleBookSuccess: 'handleBookSuccess',

      /**
       * @since [*next-version*]
       *
       * @property {VueComponent} `form-wizard` Form wizard component.
       */
      'form-wizard': 'form-wizard',

      /**
       * @since [*next-version*]
       *
       * @property {VueComponent} `tab-content` Wizard's tab content component.
       */
      'tab-content': 'tab-content',

      /**
       * @since [*next-version*]
       *
       * @property {VueComponent} `wizard-button` Wizard's button component.
       */
      'wizard-button': 'wizard-button',

      /**
       * @since [*next-version*]
       *
       * @property {object} `service-step` Service step component's definition.
       */
      'service-step': 'service-step',

      /**
       * @since [*next-version*]
       *
       * @property {object} `session-step` Session step component's definition.
       */
      'session-step': 'session-step',

      /**
       * @since [*next-version*]
       *
       * @property {object} `confirmation-step` Confirmation step component's definition.
       */
      'confirmation-step': 'confirmation-step',
    },

    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {BookableService|null} service Service that should be booked.
         */
        service: null,

        /**
         * @since [*next-version*]
         *
         * @property {BookingSession|null} session Selected booking session.
         */
        session: null,

        /**
         * @since [*next-version*]
         *
         * @property {boolean} isCreatingBooking Indicates that component is creating booking now.
         */
        isCreatingBooking: false,

        /**
         * @since [*next-version*]
         *
         * @property {string} errorMessage Error message if booking is not created.
         */
        errorMessage: null,

        /**
         * @since [*next-version*]
         *
         * @property {object} filterValues Selected booking filters.
         */
        filterValues: {}
      }
    },

    watch: {
      service: {
        deep: true,
        immediate: true,
        /**
         * Watch for service change and use new service's timezone for
         * appointment timezone when service is changed. The timezone will not change
         * automatically with the service if it was manually selected to be something else.
         *
         * @since [*next-version*]
         *
         * @param {BookableService|null} newService New selected service
         * @param {BookableService|null} oldService Previous selected service
         */
        handler (newService, oldService) {
          if (!newService) {
            return
          }
          if (!oldService || this.timezone === oldService.timezone) {
            this.timezone = newService.timezone
          }
        }
      }
    },

    props: {
      /**
       * Configuration for wizard.
       *
       * @since [*next-version*]
       *
       * @property {object} config Wizard configuration.
       * @property {BookableService|null} config.service Preselected service for booking.
       * @property {string|null} config.redirectUrl Page on which user should be redirected after successful book.
       */
      config: {
        default () {
          return {
            /**
             * @since [*next-version*]
             *
             * @var {BookableService|null} service Preselected service for booking.
             */
            service: null,

            /**
             * @since [*next-version*]
             *
             * @var {string|null} redirectUrl Page on which user should be redirected after successful book.
             */
            redirectUrl: null
          }
        }
      }
    },

    computed: {
      /**
       * @since [*next-version*]
       *
       * @property {{isOtherSessionsAvailable: bool, pricePreview: string}} serviceInfo Description of selected service.
       */
      serviceInfo () {
        if (!this._minSessionLength) {
          return
        }

        return {
          isOtherSessionsAvailable: Object.keys(this.service.sessionTypes).length > 1,
          pricePreview: this.getLabel('preview.price', [
            this._minSessionLength.price.formatted,
            this.nonPluralHumanizeDuration(this._minSessionLength.data.duration * 1000)
          ])
        }
      },

      /**
       * @since [*next-version*]
       *
       * @property {SessionLength} _minSessionLength Min session of given service.
       */
      _minSessionLength () {
        if (!this.service) {
          return
        }
        return this.service.sessionTypes.reduce((p, v) => p.data.duration < v.data.duration ? p : v)
      },

      /**
       * @since [*next-version*]
       *
       * @return {object} Booking fields from state.
       */
      bookingState () {
        return this.$store.state.booking
      }
    },

    /**
     * Hook, runs when component is created. It will select service, if service
     * is passed to wizard configuration.
     *
     * @since [*next-version*]
     */
    created () {
      if (this.config.service) {
        this.service = this.config.service
      }

      this._hydrateStore()
    },

    methods: {
      /**
       * Update filter values.
       *
       * @since [*next-version*]
       *
       * @param values
       */
      updateFilterValues (values) {
        this.filterValues = values
      },

      /**
       * Create booking on server and redirect user to cart, if url is
       * passed to component's config.
       *
       * @since [*next-version*]
       *
       * @return {Promise<any>} Booking creation promise object.
       */
      createBooking () {
        this.isCreatingBooking = true
        return this.bookingsApi.create(Object.assign({}, {
          start: this.session.start,
          end: this.session.end,
          service: this.service.id,
          resources: this.session.resources,
          transition: this.configuration.initialBookingTransition,
        }, this.bookingState)).then((response) => {
          this.isCreatingBooking = false
          this.handleBookSuccess(response)
        }, this.handleBookError)
      },

      /**
       * Handler for case when booking was not created.
       *
       * @since [*next-version*]
       */
      handleBookError (error) {
        this.isCreatingBooking = false
        const responseData = error.response.data
        this.errorMessage = responseData.data.errors[0] || responseData.message
      },

      /**
       * Can user switch from service selection tab.
       *
       * @since [*next-version*]
       *
       * @return {boolean} Can user switch from service tab.
       */
      isServiceStepReady () {
        return !!this.service
      },

      /**
       * Can user switch from session selection tab.
       *
       * @since [*next-version*]
       *
       * @return {boolean} Can user switch from session selection tab.
       */
      isSessionStepReady () {
        return !!this.session
      },

      /**
       * Make sure that wizard top point in the viewport when step changes.
       *
       * @since [*next-version*]
       */
      onStepChange () {
        const wizardElementViewportPosition = this.$refs.wizard.$el.getBoundingClientRect()
        if (wizardElementViewportPosition.y < -110) {
          window.scrollTo(window.pageXOffset, window.pageYOffset + wizardElementViewportPosition.y)
        }
      },

      /**
       * Set application's store structure according configuration. This is
       * required because Vuex works with defined state structure.
       *
       * Bookings store state structure is defined in the `bookingDataMap` var.
       *
       * @since [*next-version*]
       */
      _hydrateStore () {
        this.$store.replaceState(Object.assign({}, this.$store.state, {
          booking: Object.values(bookingDataMap).reduce((obj, key) => {
            obj[key] = null
            return obj
          }, {}),
          settings: this._getSettings()
        }))
      },

      /**
       * Get settings for wizard. Settings are coming from component's attributes
       * that are not registered as props.
       *
       * @since [*next-version*]
       *
       * @return {object} Prepared settings holder.
       */
      _getSettings () {
        return Object.keys(this.$attrs).reduce((settings, key) => {
          settings[this.kebabToCamelCase(key)] = this.$attrs[key]
          return settings
        }, {})
      },
    },

    components: {
      'form-wizard': 'form-wizard',
      'tab-content': 'tab-content',
      'wizard-button': 'wizard-button',
      'service-step': 'service-step',
      'session-step': 'session-step',
      'confirmation-step': 'confirmation-step',
    }
  }
}
