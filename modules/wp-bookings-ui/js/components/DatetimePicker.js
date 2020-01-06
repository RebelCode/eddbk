export function CfDatetimePicker (DatetimePicker, moment, format) {
  return DatetimePicker.extend({
    template: '#datepicker-template',
    inject: [
      'datepicker',
      'time-picker'
    ],
    props: {
      /**
       * @property {string} Moment-ish format for input
       */
      dataFormat: {
        default: format
      },

      /**
       * Date format for date picker
       */
      dateFormat: {
        type: String,
        default: 'dd/MM/yyyy'
      },

      /**
       * Date before all days is disabled
       */
      disabledBefore: {
        default: false
      },

      /**
       * Is time part of input is disabled.
       */
      timeDisabled: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      /**
       * Time value for project's vue time picker
       */
      timeValue: {
        get () {
          if (!this.value || this.timeDisabled) {
            return {
              HH: 0,
              mm: 0,
              ss: 0
            }
          }

          const datetime = moment(this.value)
          return {
            HH: datetime.format('HH'),
            mm: datetime.format('mm')
          }
        },

        set (newValue) {
          const datetime = moment(this.value || moment())

          datetime.set({
            hour: newValue.HH,
            minute: newValue.mm,
            second: 0
          })

          this.$emit('input', datetime.format(this.dataFormat))
        }
      },

      /**
       * Date value for project's vue time picker
       */
      dateValue: {
        get () {
          if (!this.value) return null

          return moment(this.value).startOf('day').toDate()
        },

        set (newValue) {
          const value = moment(this.value || moment())
          newValue = moment(newValue)

          value.set({
            year: newValue.year(),
            month: newValue.month(),
            date: newValue.date(),
            second: 0
          })

          if (!this.value) {
            const isToday = value.isSame(moment(), 'day')
            const hour = isToday ? value.hour : 0
            const minute = isToday ? value.minute : 0
            value.set({
              hour,
              minute
            })
          }

          this.$emit('input', value.format(this.dataFormat))
        }
      },

      /**
       * Disabled dates configuration for `datepicker` component.
       *
       * @property {{to: Date}}|{{}}
       */
      disabledDates () {
        if (!this.disabledBefore) {
          return {}
        }
        /**
         * @var {moment} selectedDate
         */
        const selectedDate = moment(this.disabledBefore)
        selectedDate.set({
          hour: 0,
          minute: 0,
          second: 0,
        })

        return {
          to: selectedDate.toDate()
        }
      },

      disabledTime () {
        if (!this.disabledBefore || !this.value) {
          return
        }
        let onDateWhenShouldDisable = this.dateValue === moment(this.disabledBefore).format('YYYY-MM-DD')
        if (!onDateWhenShouldDisable) {
          return
        }

        const datetime = moment(this.disabledBefore)

        return {
          HH: datetime.format('HH'),
          mm: datetime.format('mm')
        }
      }
    },
    mounted () {
      this.$refs.timepicker.$el.querySelector('input').onclick = (e) => {
        e.target.select()
      }
    },
    methods: {
      openDatepicker () {
        this.$refs.datepicker.$refs.dateInput.click()
      },
      closeDatepicker () {
        this.$refs.datepicker.close(true)
      },
      datepickerOpened () {
        this.$refs.datepicker.$refs.dateInput.select()
      },
      dateSelected () {
        if (this.timeDisabled) {
          return
        }
        this.$refs.timepicker.toggleDropdown()
        this.$refs.timepicker.$el.querySelector('input').select()
      }
    },
    components: {
      datepicker: 'datepicker',
      'time-picker': 'time-picker'
    }
  })
}