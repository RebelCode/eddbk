export default function (VModelProxy, TranslateCapable, MapBookingFieldsCapable) {
  return {
    template: '#eddbk-session-step-template',

    mixins: [ VModelProxy, TranslateCapable, MapBookingFieldsCapable ],

    inject: {
      /**
       * @since [*next-version*]
       *
       * @property {object} `service-session-selector` Service session selector component's definition.
       */
      'service-session-selector': 'service-session-selector',

      /**
       * @since [*next-version*]
       *
       * @property {object} `timezone-select` Timezone selector component's definition.
       */
      'timezone-select': 'timezone-select',

      /**
       * @since [*next-version*]
       *
       * @property {object} state The application's state.
       */
      state: 'state',
    },

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {BookableService|null} service Service that can be booked.
       */
      service: {
        default: null
      },

      /**
       * @since [*next-version*]
       *
       * @property {BookingSession} value Selected booking session, model for current component.
       */
      value: {}
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
        this.$emit('exposed', values)
      },
    },

    components: {
      'service-session-selector': 'service-session-selector',
      'timezone-select': 'timezone-select'
    }
  }
}