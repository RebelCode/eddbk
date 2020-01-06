import template from './template.html'
import { guessUnit } from '../../libs/sessionHelpers'
import GetResourceCapable from './../../mixins/GetResourceCapable'

/**
 * The services list component.
 *
 * @since [*next-version*]
 *
 * @param mapStore
 *
 * @return {*}
 */
export default function (makeItemsListMixin, mapStore) {
  return {
    ...template,
    mixins: [
      makeItemsListMixin(mapStore, 'services'),
      GetResourceCapable
    ],
    inject: {
      'v-switch': 'v-switch',
      'humanizeDuration': 'humanizeDuration',
    },
    data () {
      return {
        statusesTransitionMap: {
          publish: 'draft',
          draft: 'publish'
        }
      }
    },
    methods: {
      /**
       * Get sessions duration preview.
       *
       * @since [*next-version*]
       *
       * @param {object[]} sessions List of available session lengths.
       */
      getDurationPreview (sessions) {
        if (sessions.length === 1) {
          return this.humanizeDuration(sessions[0].data.duration * 1000)
        }
        const last = sessions.length - 1
        if (this.isSessionLengthUnitsAreSame(sessions[0].data.duration, sessions[last].data.duration)) {
          return this.humanizeDuration(sessions[0].data.duration * 1000).split(' ')[0] + '-' + this.humanizeDuration(sessions[last].data.duration * 1000)
        }
        return this.humanizeDuration(sessions[0].data.duration * 1000) + ' - ' + this.humanizeDuration(sessions[last].data.duration * 1000)
      },

      isSessionLengthUnitsAreSame (a, b) {
        const aUnit = guessUnit(a)
        const bUnit = guessUnit(b)
        return aUnit !== false && bUnit !== false && aUnit === bUnit
      },

      /**
       * Get sessions price preview.
       *
       * @since [*next-version*]
       *
       * @param {object[]} sessions List of available session lengths.
       */
      getPricePreview (sessions) {
        if (sessions.length === 1) {
          return sessions[0].price.formatted.replace('.00', '')
        }
        const last = sessions.length - 1
        return sessions[0].price.formatted.replace('.00', '') + ' - ' + sessions[last].price.formatted.replace('.00', '')
      },

      /**
       * Switch service's status.
       *
       * @since [*next-version*]
       *
       * @param {object} service
       */
      switchServiceStatus (service) {
        service.status = this.statusesTransitionMap[service.status]
        this.$emit('save', service)
      }
    },
    components: {
      'v-switch': 'v-switch',
    },
  }
}
