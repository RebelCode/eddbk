export default function CfServiceAvailabilityEditor (AbstractEntityModalEditor, { mapState, mapMutations }, moment, FunctionalArrayCollection) {
  const helpers = {
    /**
     * Create date object from string in format YYYY-MM-DD
     *
     * @param date
     * @return {Date}
     */
    getDate (date) {
      return moment(date).toDate()
    }
  }

  return AbstractEntityModalEditor.extend({
    template: '#service-availability-editor-template',
    inject: {
      'stringHelpers': 'stringHelpers',

      'momentHelpers': 'momentHelpers',

      /**
       * @since [*next-version*]
       *
       * @property {AvailabilityHelpers} availabilityHelpers Helper functions for availabilities.
       */
      'availabilityHelpers': 'availabilityHelpers',

      'weekStartsOnIndex': 'weekStartsOnIndex',

      'pluralize': 'pluralize',
      'config': 'config',

      modalState: {
        from: 'availabilityEditorStateToggleable'
      },

      '_': {
        from: 'translate'
      },

      'inline-editor': 'inline-editor',
      repeater: 'repeater',
      datepicker: 'datepicker',
      'datetime-picker': 'datetime-picker',
      'time-picker': 'time-picker',

      'selection-list': 'selection-list'
    },
    data () {
      return {
        model: {
          id: null,

          start: null,
          end: null,

          isAllDay: false,

          repeat: false,
          repeatPeriod: 1,
          repeatUnit: 'days', // | "weeks" | "months" | "years"
          repeatUntil: 'period', // | "date"
          repeatUntilPeriod: 1,
          repeatUntilDate: null,

          repeatWeeklyOn: [],
          repeatMonthlyOn: [], // "day_of_week" | "date_of_month"

          excludeDates: []
        },

        exclusionsPickerVisible: false,

        excludeDatesCollection: new FunctionalArrayCollection(() => {
          return this.model.excludeDates.sort()
        }, (newDates) => {
          this.model.excludeDates = newDates
        }, (date) => {
          return date
        }),

        repeatsOnCollection: new FunctionalArrayCollection(() => {
          return this.repeatsOn
        }, (newValue) => {}, (item) => {
          return item.key
        })
      }
    },
    watch: {
      /**
       * Set end time when user it is empty and user set start time
       *
       * @param newValue
       */
      'model.start': function (newValue) {
        if (!this.model.end) {
          this.model.end = newValue
        }
      },

      /**
       * Watch for isAllDay flag change and subtract one day from real end datetime when
       * this flag is turned off, and add one day of it is turned on.
       *
       * @since [*next-version*]
       *
       * @param {boolean} newValue New isAllDay value.
       */
      'model.isAllDay': function (newValue) {
        if (this.seedLock) return

        const tzFree = this.config.formats.datetime.tzFree
        if (newValue) {
          this.model.end = moment(this.model.end).endOf('day').add(1, 'second').format(tzFree)
        }
        else {
          this.model.end = moment(this.model.end).subtract(1, 'second').format(tzFree)
        }
      },

      /**
       * Watch by any availability change and fix repeat period if it is out
       * of range (smaller that availability duration, or bigger than allowed for repeat unit).
       */
      'model': {
        deep: true,
        handler: function () {
          if (this.seedLock) return
          this.normalizeRepeatPeriod()
        }
      },

      /**
       * Change default values when repeats changes.
       */
      'model.repeatUnit': function (newValue) {
        if (this.seedLock) return

        switch (newValue) {
          case 'months':
            this.model.repeatWeeklyOn = []
            this.model.repeatMonthlyOn = [ 'day_of_month' ]
            break
          default:
            this.model.repeatWeeklyOn = []
            this.model.repeatMonthlyOn = []
        }
      },

      /**
       * Preserve at least one selected day when selected weeks repeating.
       */
      'model.repeatWeeklyOn': {
        deep: true,
        handler: function (newValue) {
          if (this.seedLock || !this.model.start || !this.isSameDay() || newValue.length) return

          this.model.repeatWeeklyOn = [
            moment(this.model.start).format('dddd').toLowerCase()
          ]
        }
      },
    },
    props: {
      /**
       * Collection of availabilities.
       *
       * @since [next-version]
       *
       * @var {FunctionalCollection} entitiesCollection
       */
      entitiesCollection: {},

      /**
       * @var {string} instanceLabel The label of availability instance.
       *
       * @since [*next-version*]
       */
      instanceLabel: {}
    },
    computed: {
      ...mapState({
        timezone (state) {
          return state.app.timezone || this.config.timezone
        }
      }),
      ...mapState('bookingOptions', {
        entityModel: state => state.serviceAvailabilityModel
      }),

      /**
       * Formatted model's end datetime for UI. It will display day before
       * real model's end datetime if all day is enabled.
       *
       * @since [*next-version*]
       *
       * @property {string}
       */
      visibleEnd: {
        get () {
          const ends = moment(this.model.end)
          if (this.model.isAllDay) {
            ends.subtract(1, 'day')
          }
          return ends.format(this.config.formats.datetime.tzFree)
        },

        set (value) {
          const ends = moment(value)
          if (this.model.isAllDay) {
            ends.endOf('day').add(1, 'second')
          }
          this.model.end = ends.format(this.config.formats.datetime.tzFree)
        }
      },

      /**
       * Repeat until date casted to Date type
       *
       * @property {Date}
       */
      repeatUntilDateModel: {
        get () {
          if (!this.model.repeatUntilDate) {
            return
          }
          return moment(this.model.repeatUntilDate).toDate()
        },
        set (value) {
          this.model.repeatUntilDate = this.momentHelpers
            .createInTimezone(value, this.timezone)
            .startOf('day')
            .format(this.config.formats.datetime.tzFree)
        }
      },

      /**
       * @property {Number} Minimal possible repeating period according availability duration and repeat unit.
       */
      minimalRepeatPeriod () {
        if (!this.model.start || !this.model.end) {
          return 1
        }
        return Math.ceil(moment(this.model.end).diff(moment(this.model.start), this.model.repeatUnit, true))
      },

      excludeDatesModels () {
        let dates = this.model.excludeDates.map((date) => {
          return helpers.getDate(date)
        })
        return { dates }
      },

      /**
       * Current day is used for disabling dates in start at datepicker up to this date.
       *
       * @return {Date}
       */
      currentDay () {
        let today = moment()
        today.set({
          hour: 0,
          minute: 0,
          second: 0,
        })
        return today.toDate()
      },

      /**
       * When availability starts, used for disabling dates in ends on datepicker up to start date.
       *
       * @return {Date}
       */
      availabilityStart () {
        return moment(this.model.start).toDate()
      },

      repeatingTitle () {
        return this.pluralize(this._(this.model.repeatUnit), Number(this.model.repeatPeriod))
      },

      repeatsUntilPeriodTitle () {
        return this.pluralize(this._(this.model.repeatUnit), Number(this.model.repeatUntilPeriod))
      },

      repeatingDuration () {
        switch (this.model.repeatUnit) {
          case 'days':
            return 31
          case 'weeks':
            return 52
          case 'months':
            return 12
          case 'years':
            return 5
          default:
            return 31
        }
      },

      repeatsOn () {
        if (this.model.repeatUnit !== 'weeks' && this.model.repeatUnit !== 'months') {
          return []
        }

        let start = moment(this.model.start)

        const defaultWeekDays = {
          sunday: 'S', // @todo: FIX IN ALPHA
          monday: this._('M'),
          tuesday: this._('T'),
          wednesday: this._('W'),
          thursday: this._('T'),
          friday: this._('F'),
          saturday: 'S', // @todo: FIX IN ALPHA
        }
        let weeks = [];
        let weekDays = Object.keys(defaultWeekDays)
        const rotate = (array, times) => {
          times = Math.abs(times)
          while (times--) {
            var temp = array.shift()
            array.push(temp)
          }
        }
        rotate(weekDays, this.weekStartsOnIndex)
        for (let weekDay of weekDays) {
          weeks[weekDay] = defaultWeekDays[weekDay]
        }

        let repeatsOptions = {
          weeks,
          months: {
            day_of_month: start ? this._('Monthly on day %s', [start.format('D')]) : '',
            day_of_week: start ? this._('Monthly on the %s %s', [this.momentHelpers.weekdayInMonthNumber(start), start.format('dddd')]) : '',
          }
        }

        let makeKeyValueArray = (obj) => {
          return Object.keys(obj).map((key) => {
            return {
              key,
              value: obj[key]
            }
          })
        }

        return makeKeyValueArray(repeatsOptions[this.model.repeatUnit])
      }
    },
    methods: {
      ...mapMutations({
        setNewEntities: 'setNewAvailabilities'
      }),

      _getExclusionItemTitle (date) {
        return helpers.getDate(date).toLocaleDateString('en-US', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})
      },

      /**
       * Set correct repeat period according availability duration and repeat unit.
       */
      normalizeRepeatPeriod () {
        if (!this.model.start || !this.model.end) {
          return
        }

        if (this.model.repeatPeriod < this.minimalRepeatPeriod) {
          this.model.repeatPeriod = this.minimalRepeatPeriod
          return
        }

        if (this.model.repeatPeriod > this.repeatingDuration) {
          this.model.repeatPeriod = this.repeatingDuration
        }
      },

      /**
       * Check that availability start is on the same day with availability end.
       *
       * @since [*next-version*]
       *
       * @return {boolean} Is availability start and end on the same day.
       */
      isSameDay () {
        if (!this.model.start || !this.model.end) {
          return false
        }
        return this.availabilityHelpers.isOneDay(this.model)
      },

      /**
       * Is entity can be updated or deleted.
       *
       * @return {bool}
       */
      entityCanBeModified () {
        return true
      },

      excludeDateSelected (date) {
        date = moment(date).startOf('day').format(this.config.formats.datetime.tzFree)

        if (this.excludeDatesCollection.hasItem(date)) {
          this.excludeDatesCollection.removeItem(date)
        }
        else {
          this.excludeDatesCollection.addItem(date)
        }

        this.$refs.exclusions.selectedDate = null
      },

      /**
       * Fires when availability start value is changed.
       * Here we are trying to make sure that start datetime is not
       * bigger than end datetime. If validation fails we show error in UI.
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
       * Validate current availability and if everything is fine, save it.
       */
      saveAvailability () {
        this.$validator.validateAll().then((result) => {
          if (!result) {
            return
          }
          this.saveItem()
        })
      }
    },
    components: {
      repeater: 'repeater',
      'inline-editor': 'inline-editor',
      datepicker: 'datepicker',
      'datetime-picker': 'datetime-picker',
      'time-picker': 'time-picker',
      'selection-list': 'selection-list'
    }
  })
}