export default function (TranslateCapable, CreateDatetimeCapable, MapBookingFieldsCapable, dateFormats) {
  return {
    template: '#eddbk-confirmation-step-template',

    mixins: [ TranslateCapable, CreateDatetimeCapable, MapBookingFieldsCapable ],

    inject: {
      /**
       * @since [*next-version*]
       *
       * @property {Function} nonPluralHumanizeDuration Function for humanizing durations.
       */
      nonPluralHumanizeDuration: 'nonPluralHumanizeDuration'
    },

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {BookableService|null} service Service that should be booked.
       */
      service: {},

      /**
       * @since [*next-version*]
       *
       * @property {BookingSession|null} session Selected booking session.
       */
      session: {},

      /**
       * @since [*next-version*]
       *
       * @property {object} filterValues Selected booking filters.
       */
      filterValues: {}
    },

    computed: {
      /**
       * @since [*next-version*]
       *
       * @property {object} appointment Summary information for appointment.
       */
      appointment () {
        if (!this._selectedSessionDuration) {
          return
        }

        let duration = this.nonPluralHumanizeDuration(this.session.duration * 1000)

        let sessionType = this.filterValues.duration
        if (sessionType.label) {
          duration = `${sessionType.label} (${duration})`
        }

        let staffMember = null
        if (this.filterValues.staffMember) {
          staffMember = this.filterValues.staffMember.name
        }

        return {
          service: this.service.name,
          price: this._selectedSessionDuration.price.formatted,
          start: this.createLocalDatetime(this.session.start).format(dateFormats.appointmentStart),
          duration,
          staffMember
        }
      },

      /**
       * @since [*next-version*]
       *
       * @property {SessionLength} _selectedSessionDuration Selected session length information.
       */
      _selectedSessionDuration () {
        if (!this.session) {
          return
        }
        return this.service.sessionTypes.find(sessionType => sessionType.data.duration === this.session.duration)
      }
    }
  }
}
