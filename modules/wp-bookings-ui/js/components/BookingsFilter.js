/**
 * Component for filtering bookings by status and client.
 *
 * @param {function} mapState Helper method for creating state getters.
 * @param {function} mapMutations Helper method for creating methods for mutations.
 * @param {typeof FunctionalCollection} FunctionalCollection constructor.
 *
 * @return {Component}
 *
 * @constructor
 */
export function CfBookingsFilter ({ mapState, mapMutations }, FunctionalCollection) {
  return {
    template: "#bookings-filter-template",
    inject: {
      'selection-list': 'selection-list',
      '_': {
        from: 'translate'
      },
    },
    props: {
      searchString: {},
      status: {}
    },
    data () {
      return {
        items: new FunctionalCollection(() => {
          let resultStatuses = {}
          let selectedStatuses = 'all'
          resultStatuses[selectedStatuses] = {
            id: selectedStatuses,
            title: this._('All'),
            count: 0
          }
          Object.keys(this.statuses).filter(key => {
            return !!this.appStatuses[key] && this.screenStatuses.indexOf(key) !== -1
          }).map(key => {
            resultStatuses[key] = {
              id: key,
              title: this.appStatuses[key],
              count: Number(this.statuses[key])
            }
            resultStatuses[selectedStatuses].count += Number(this.statuses[key])
          })
          this.setBookingsCount(resultStatuses[this.status].count)
          return resultStatuses
        }, (statuses) => {
          this.setBookingsStatuses(statuses)
        }, (status) => {
          return status.id
        })
      }
    },
    computed: {
      ...mapState({
        appStatuses: state => state.app.statuses,
        screenStatuses: state => state.app.screenStatuses
      }),
      ...mapState('bookings', [
        'bookingsCount',
        'statuses'
      ]),

      /**
       * Search string model. It emits update event on set to
       * simplify usage, so consumer can write:
       *
       * `:search-string.sync="searchString"`
       *
       * @see https://vuejs.org/v2/guide/components-custom-events.html#sync-Modifier
       *
       * @property {string}
       */
      searchStringModel: {
        get () {
          return this.searchString
        },
        set (value) {
          this.$emit('update:searchString', value)
        }
      },

      /**
       * Status model for selection list.
       *
       * @property {array}
       */
      statusModel: {
        get () {
          return [this.status]
        },
        set (value) {
          this.$emit('update:status', value[0])
          this.submit()
        }
      },
    },
    methods: {
      ...mapMutations('bookings', [
        'setBookingsStatuses',
        'setBookingsCount',
      ]),

      /**
       * Fire submit event, so listener can request new
       * data according filter values.
       */
      submit () {
        this.$emit('submit')
      }
    },
    components: {
      'selection-list': 'selection-list'
    }
  }
}