import bookings from './bookings'
import bookingOptions from './booking-options'
import ui from './ui'
import settings from './settings'
import { store as services } from './../modules/services'
import { store as staffMembers } from './../modules/staffMembers'

const state = {
  app: {
    timezone: 'UTC+0',
    bookings: [],
    screenOptionsEndpoint: '',
    statuses: {},
    screenStatuses: []
  },
}

const mutations = {
  setInitialState (state, appState) {
    state.app = Object.assign({}, state.app, appState)

    /*
     * Set initial timezone value for bookings page.
     */
    state.bookings.timezone = appState.bookingsTimezone || appState.config.timezone
  },

  setTimezone (state, value) {
    state.app.timezone = value
  },

  setNewBookings (state, bookings) {
    state.app.bookings = bookings
  },

  setScreenStatuses (state, statuses) {
    state.app.screenStatuses = statuses.slice()
  }
}

export default function ({deepHas, deepSet}) {
  return {
    modules: {
      bookings,
      bookingOptions,
      ui,
      settings,
      services,
      staffMembers,
    },
    state,
    mutations: Object.assign({}, mutations, {
      /**
       * Deep set value of state.
       *
       * @since [*next-version*]
       *
       * @param {object} state Root store's state.
       * @param {string} key Key to set.
       * @param {*} value Value to set.
       */
      set (state, {key, value}) {
        if (!deepHas(state, key)) {
          throw new Error(`Can't set value. Key "${key}" doesn't exist.`)
        }
        deepSet(state, key, value)
      }
    })
  }
}