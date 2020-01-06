export function CfVueTimepicker(VueTimepicker) {
  return VueTimepicker.extend({
    template: '#vue-timepicker-template',
    props: {
      id: {},
      /**
       * Disable time picking before this time
       */
      disabledTo: {
        type: Object
      },

      /**
       * Is time field is disabled
       */
      disabled: {
        type: Boolean,
        default: false
      }
    },
    watch: {
      showDropdown (value) {
        if (value) {
          this.$nextTick(() => {
            this._scrollToSelected()
          })
        }
      },
      disabled (value) {
        if (!value) {
          return
        }
        this.hour = ''
        this.minute = ''
        this.amp = ''
      }
    },
    computed: {
      displayTime () {
        let formatString = String((this.format || 'HH:mm'))
        if (this.hour) {
          formatString = formatString.replace(new RegExp(this.hourType, 'g'), this.hour)
        }
        if (this.minute) {
          formatString = formatString.replace(new RegExp(this.minuteType, 'g'), this.minute)
        }
        if (this.second && this.secondType) {
          formatString = formatString.replace(new RegExp(this.secondType, 'g'), this.second)
        }
        if (this.apm && this.apmType) {
          formatString = formatString.replace(new RegExp(this.apmType, 'g'), this.apm)
        }

        formatString = formatString.replace(new RegExp(this.hourType, 'g'), '--')
          .replace(new RegExp(this.minuteType, 'g'), '--')

        return formatString
      },

      disabledValues () {
        const def = { hour: [], minute: [], second: [], apm: [] }

        if (!this.disabledTo) {
          return def
        }

        let hour = [], minute = []

        const hourValue = this.hour ? Number(this.hour) : false

        const disableToHour = Number(this.disabledTo[this.hourType])
        const disableToMinute = Number(this.disabledTo[this.minuteType])

        hour = Array.apply(null, {length: disableToHour}).map(Number.call, Number)
        if (disableToMinute === 59) {
          hour.push(disableToHour)
        }

        if (hourValue === disableToHour) {
          minute = Array.apply(null, {length: disableToMinute + 1}).map(Number.call, Number)
        }

        return { hour, minute, second: [], apm: [] }
      }
    },
    methods: {
      /**
       * Scroll time pickers to selected values.
       *
       * @private
       */
      _scrollToSelected () {
        const scrollLists = [
          'hours', 'minutes', 'seconds', 'apms'
        ]
        const scrollHolder = this.$el.querySelector(`.select-list`)
        if (!scrollHolder) {
          return
        }
        const holderHeight = scrollHolder.getBoundingClientRect().height
        for (let listClass of scrollLists) {
          const el = scrollHolder.querySelector(`.${listClass}`)
          if (!el) {
            continue
          }
          const activeNode = el.querySelector('.active')
          if (!activeNode) {
            continue
          }
          el.scrollTop = activeNode.offsetTop - holderHeight / 2 + 15
        }
      },

      /**
       * Toggle timepicker dropdown
       */
      toggleDropdown () {
        this.showDropdown = !this.showDropdown
        this.$emit(this.showDropdown ? 'opened' : 'closed')
      },
    }
  })
}