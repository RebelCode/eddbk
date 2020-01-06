/**
 * Calendar for rendering availabilities in format suitable
 * for customer.
 *
 * Customer will see how exactly availabilities will be available
 * across days.
 *
 * @param FullCalendar
 * @param moment
 * @return {*}
 */
export default function (FullCalendar, moment) {
  return FullCalendar.extend({
    inject: {
      'availabilitiesCollection': 'availabilitiesCollection',

      'momentHelpers': 'momentHelpers',

      'defaultCalendarView': 'defaultCalendarView',

      'weekStartsOnIndex': 'weekStartsOnIndex',

      /**
       * @since [*next-version*]
       *
       * @property {AvailabilityHelpers} availabilityHelpers Helper functions for availabilities.
       */
      'availabilityHelpers': 'availabilityHelpers',

      /**
       * Function for rendering availability event template.
       *
       * @since [*next-version*]
       *
       * @property {TemplateRenderFunction} renderAvailabilityEventTemplate
       */
      renderAvailabilityEventTemplate: 'renderAvailabilityEventTemplate',

      'appConfig': {
        from: 'config'
      }
    },
    data () {
      return {
        rangeStart: null,
        rangeEnd: null,
        generatedAvailabilities: [],
        availabilitiesDurationsCache: {}
      }
    },
    props: {
      availabilities: {
        default () {
          return []
        }
      },
      overlappingAvailabilities: {
        type: Boolean,
        default: false,
      },
      header: {
        default () {
          return {
            left: 'agendaWeek,month today',
            right: 'title prev,next'
          }
        },
      },
      defaultView: {
        default () {
          return this.defaultCalendarView === 'agendaDay' ? 'agendaWeek' : this.defaultCalendarView
        },
      },
      visibleTimezone: {
        default: false
      },
      config: {
        type: Object,
        default () {
          let self = this
          return {
            contentHeight: 350,
            slotDuration: '02:00:00',
            slotLabelInterval: '04:00:00',
            firstDay: this.weekStartsOnIndex,
            viewRender (view) {
              /*
               * Switch start and end to not extended moment object.
               *
               * `view.start` and `view.end` are instances of moment that is extended by FullCalendar.
               * They wrapped in `moment` constructor to behave like a normal moment object, not
               * like "Ambiguously-timed Moments".
               *
               * @see https://github.com/RebelCode/bookings-js/pull/79/files#r215881944
               */
              self.rangeStart = moment(view.start.format())
              self.rangeEnd = moment(view.end.format())

              self.renderRepeatedAvailabilities()
            },
            selectAllow (selectInfo) {
              return selectInfo.start.isSameOrAfter(moment(), 'day')
            }
          }
        },
      },
    },

    computed: {
      generatedEvents () {
        return this.generatedAvailabilities
      }
    },

    watch: {
      availabilities: {
        deep: true,
        handler () {
          if (!this.rangeStart) return
          this.renderRepeatedAvailabilities()
        }
      },
      visibleTimezone () {
        this.renderRepeatedAvailabilities()
      },
      generatedAvailabilities (value) {
        let overlapping = {}
        for (let availability of value) {
          if (!overlapping[availability.start]) {
            overlapping[availability.start] = 0
          }
          if (availability.model.isAllDay) {
            overlapping[availability.start]++
          }
          if (overlapping[availability.start] > 1) {
            this.$emit('update:overlappingAvailabilities', true)
            return
          }
        }
        this.$emit('update:overlappingAvailabilities', false)
      }
    },

    mounted () {
      this.$on('event-created', this.eventCreated)
      this.$on('event-selected', this.eventClicked)
      this.$on('event-render', this.eventRender)
    },

    methods: {
      /**
       * Render event view.
       *
       * @param event
       * @param element
       * @param view
       */
      eventRender (event, el, view) {
        const endDate = event.end || event.start
        const timezone = this.visibleTimezone === this.appConfig.timezone ? '' : this.momentHelpers.timezoneLabel(this.visibleTimezone)
        if (endDate.isBefore(moment(), 'day')) {
          el[0].classList.add('fc-event--past')
        }

        el.find('.fc-content')
          .addClass(`rc-event`)
          .html(this.renderAvailabilityEventTemplate({
            start: event.start.format('HH:mm'),
            end: event.end.format('HH:mm'),
            timezone
          }))
      },

      /**
       * Render availabilities.
       *
       * It will render each availability repeated
       * according availability repeating rules.
       *
       * It will help customer to see exactly on which days and how
       * sessions are available.
       */
      renderRepeatedAvailabilities () {
        if (!this.availabilities.map) return
        let repeatedAvailabilities = []

        for (let m = moment(this.rangeStart); m.diff(this.rangeEnd, 'days') <= 0; m.add(1, 'days')) {
          repeatedAvailabilities = repeatedAvailabilities.concat(
            this.getAvailabilitiesForDay(moment(m))
          )
        }
        repeatedAvailabilities = repeatedAvailabilities.concat(
          this.getOutOfViewAvailabilities(repeatedAvailabilities)
        )

        this.generatedAvailabilities = repeatedAvailabilities
      },

      /**
       * Get all availabilities that is out of current view.
       *
       * For example, we have week view (7 days) and availability with 9 days duration (viewDays + 2).
       *
       * There is possible case when availability can start before view and end out of view. This method
       * is finding this availabilities.
       *
       * @return {Array}
       */
      getOutOfViewAvailabilities (repeatedAvailabilities) {
        const currentViewLength = Math.abs(this.rangeStart.diff(this.rangeEnd, 'day'))

        const longAvailabilities = this.availabilities.filter(availability => {
          /*
           * Filter 1: Select ONLY long availabilities
           */
          let availabilityDurationInDays = this.getAvailabilityDaysDuration(availability)
          return availabilityDurationInDays - currentViewLength >= 2
        }).filter(availability => {
          /*
           * Filter 2: Select only availabilities that is NOT rendered for now
           */
          for (let availabilityEvent of repeatedAvailabilities) {
            if (availability.id === availabilityEvent.id) {
              return false
            }
          }
          return true
        })

        return longAvailabilities.map(availability => {
          const startDay = this.findOutOfRangeAvailabilityStartDay(availability, currentViewLength)
          if (!startDay) {
            return false
          }
          return this.availabilityToEvent(availability, startDay, /*'wheat'*/)
        }).filter(availability => {
          return availability !== false
        })
      },

      /**
       * Check that availability start is somewhere out of the range
       *
       * @param availability
       * @param currentViewLength
       * @return {boolean}
       */
      findOutOfRangeAvailabilityStartDay (availability, currentViewLength) {
        const daysToCheck = this.getAvailabilityDaysDuration(availability) - currentViewLength - 1
        for (let i = 1; i <= daysToCheck; i++) {
          const checkingDay = moment(this.rangeStart).subtract(i, 'days')
          let startIsOnDay = this.checkingDateIsOnDay(
            availability,
            moment(availability.start).startOf('day'),
            checkingDay,
            true
          )

          if (!startIsOnDay) {
            continue
          }

          const startIsNotExcluded = !this.dayInExcluded(availability, checkingDay)
            && checkingDay.isSameOrAfter(moment(availability.start), 'day')
            && !this.availabilityEnded(availability, checkingDay)

          if (startIsNotExcluded) {
            return checkingDay
          }
        }
        return false
      },

      /**
       * Get availabilities that start OR finish on given day.
       *
       * @param day
       */
      getAvailabilitiesForDay (day) {
        let startAvailabilities = this.availabilities.filter((item) => {
          return this.availabilityStartFitsInDay(item, day)
        }).map((item) => {
          return this.availabilityToEvent(item, day)
        })

        return startAvailabilities.concat(this.availabilities.filter((item) => {
          return this.availabilityEndFitsInDay(
            item,
            day
          )
        }).map((item) => {
          return this.availabilityToEvent(item, moment(day).subtract(this.getAvailabilityDuration(item), 'seconds'), /*'red'*/)
        }))
      },

      /**
       * Is current availability should be rendered in this day
       *
       * @param availability
       * @param day
       * @return {*}
       */
      availabilityStartFitsInDay (availability, day) {
        let fromDate = moment(availability.start)
        fromDate.set({
          hour: 0,
          minute: 0,
          second: 0
        })

        const availabilityShouldntBeRendered = fromDate.isAfter(day, 'day')
          || this.availabilityEnded(availability, day)
          || this.dayInExcluded(availability, day)

        if (availabilityShouldntBeRendered) {
          return false
        }

        return this.checkingDateIsOnDay(availability, fromDate, day)
      },

      /**
       * Is current availability should be rendered in this day
       *
       * @param availability
       * @param day
       * @return {*}
       */
      availabilityEndFitsInDay (availability, day) {
        let checkingDate = moment(availability.end)
        let endSecondsDiff = checkingDate.diff(moment(checkingDate.startOf('day')), 'seconds')
        checkingDate.startOf('day')

        const endOnCurrentDay = this.checkingDateIsOnDay(
          availability,
          moment(availability.start).startOf('day'),
          moment(day).subtract(this.getAvailabilityDuration(availability), 'seconds').startOf('day'),
          true
        )

        if (!endOnCurrentDay) {
          return false
        }

        let startDate = moment(day)
          .add(endSecondsDiff, 'seconds')
          .subtract(this.getAvailabilityDuration(availability), 'seconds')
          .startOf('day')

        return !this.dayInViewport(startDate)
          && !this.dayInExcluded(availability, startDate)
          && !this.availabilityEnded(availability, startDate)
          && startDate.isSameOrAfter(moment(availability.start), 'day')
      },

      /**
       * Is current date in viewport
       *
       * @param date
       * @return {boolean}
       */
      dayInViewport (date) {
        return date.isSameOrAfter(this.rangeStart, 'day')
          && date.isSameOrBefore(this.rangeEnd, 'day')
      },

      /**
       * Get availability duration in seconds
       *
       * @param availability
       * @return {*}
       */
      getAvailabilityDuration (availability) {
        return this.getCachedAvailabilityDuration(availability, 'seconds', item => {
          return Math.abs(moment(item.start).diff(moment(item.end), 'seconds'))
        })
      },

      /**
       * Availability duration in days
       *
       * @param availability
       * @return {number}
       */
      getAvailabilityDaysDuration (availability) {
        return this.getCachedAvailabilityDuration(availability, 'days', item => {
          let start = moment(item.start).startOf('day')
          let end = moment(item.end)
          return Math.ceil(Math.abs(start.diff(end, 'days', true)))
        })
      },

      /**
       * Cache and get availability duration value
       *
       * @param availability
       * @param cb
       * @return {*}
       */
      getCachedAvailabilityDuration (availability, key, cb) {
        let durationKey = availability.id + key + availability.start + availability.end
        if (!this.availabilitiesDurationsCache[durationKey]) {
          this.availabilitiesDurationsCache[durationKey] = cb(availability)
        }
        return this.availabilitiesDurationsCache[durationKey]
      },

      /**
       * Check day in excluded
       *
       * @param availability
       * @param day
       * @return {boolean}
       */
      dayInExcluded (availability, day) {
        if (!availability.excludeDates || !availability.excludeDates.length)
          return false

        for (let excludedDate of availability.excludeDates) {
          if (moment(excludedDate, 'YYYY-MM-DD').isSame(day, 'day')) {
            return true
          }
        }

        return false
      },

      /**
       * Return true if availability should be rendered ion that day.
       *
       * @param availability
       * @param {moment} checkingDate This can be end date, start date or any date that should be checked by availability repeating
       * @param day
       * @return {*}
       */
      checkingDateIsOnDay (availability, checkingDate, day, log = false) {
        if (!availability.repeat) {
          return checkingDate.isSame(day, 'day')
        }

        const unitDiff = availability.repeatUnit
        const repeatUnitDiff = Math.abs(checkingDate.diff(day, unitDiff))
        if (repeatUnitDiff % availability.repeatPeriod !== 0) {
          return false
        }

        const vm = this
        const repeatingRules = {
          days () {
            return true
          },
          weeks () {
            if (!vm.isAvailabilityOnTheSameDay(availability)) {
              const render = moment(availability.start).add(repeatUnitDiff, unitDiff).isSame(day, 'day')
              return render
            }
            return availability.repeatWeeklyOn.indexOf(day.format('dddd').toLowerCase()) > -1
          },
          months () {
            let mode = availability.repeatMonthlyOn[0]
            return mode === 'day_of_week' ? vm.momentHelpers.weekdayOfMonthIsSame(checkingDate, day) : checkingDate.format('D') === day.format('D')
          },
          years () {
            return checkingDate.format('DD/MM') === day.format('DD/MM')
          }
        }

        return repeatingRules[availability.repeatUnit] ? repeatingRules[availability.repeatUnit]() : false
      },

      /**
       * Check that availability start is on the same day with availability end.
       *
       * @since [*next-version*]
       *
       * @param {Availability} availability Availability for check.
       *
       * @return {boolean} Is availability start and end on the same day.
       */
      isAvailabilityOnTheSameDay (availability) {
        return this.availabilityHelpers.isOneDay(availability)
      },

      /**
       * Is availability repeating ended.
       *
       * This is determined by the ending date or by the weeks count,
       * after which availability becomes not available.
       *
       * @param availability
       * @param day
       * @return {*}
       */
      availabilityEnded (availability, day) {
        if (!availability.repeat) {
          return false
        }
        const fromDate = moment(availability.start)
        fromDate.set({
          hour: 0,
          minute: 0,
          second: 0
        })
        if (availability.repeatUntil === 'period') {
          return moment(fromDate)
            .add(availability.repeatUntilPeriod, availability.repeatUnit)
            .isBefore(day, availability.repeatUntilPeriod)
        }
        else if (availability.repeatUntil === 'date') {
          return moment(availability.repeatUntilDate).isBefore(moment(day).startOf('day'), 'day')
        }
        return false
      },

      /**
       * Convert availability model to event format that understandable by
       * calendar. This is required to not force backend to prepare data
       * for the concrete calendar implementation.
       *
       * @param availability
       * @return {{id: null, allDay: *, start: string, end: string, model: {} & any}}
       */
      availabilityToEvent (availability, day, _color = false) {
        let model = Object.assign({}, availability)

        let availabilityDuration = moment(availability.end).diff(moment(availability.start), 'seconds'),
          availabilityStartTime = moment(availability.start).format('HH:mm:ss')

        let eventStart = day.format('YYYY-MM-DD') + 'T' + availabilityStartTime,
          eventEnd = moment.utc(eventStart).add(availabilityDuration, 'seconds').format('YYYY-MM-DD\THH:mm:ss')

        /*
         * Availability is rendered as all day when "all day" flag explicitly checked in the availability editor,
         * or when availability's start and end time is midnight on different days.
         */
        let allDay = model.isAllDay || (moment(eventStart).format('HH:mm:ss') === '00:00:00' && moment(eventEnd).format('HH:mm:ss') === '00:00:00')

        return Object.assign({}, {
          id: model.id,
          editable: false, // disable dragging and resizing
          start: eventStart,
          allDay,
          end: eventEnd,
          model
        }, _color ? {
          color: _color,
        } : {})
      },

      /**
       * Create event when user clicks on calendar day, or
       * select some duration on calendar, or user click
       * on floating Add button.
       *
       * @param params {{start:moment, end:moment, allDay:boolean}} Params, coming from full calendar.
       */
      eventCreated (params) {
        this.fireMethod('unselect')

        let event = {id: null}

        if (params) {
          let {start, end, allDay} = params

          event = Object.assign({}, event, {
            start: start.format(),
            end: end.format(),
            isAllDay: allDay,
          })
        }

        this.$emit('availability-click', event)
      },

      /**
       * Triggered when user clicks on event in calendar.
       *
       * @param event
       */
      eventClicked (event) {
        this.$emit('availability-click', Object.assign({}, event.model))
      }
    },

    render (h) {
      return h('div', {
        ref: 'calendar'
      })
    }
  })
}