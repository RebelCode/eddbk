import makeStoreObject from './store'
import api from './api'
import libs from './libs'
import components from './components'
import transformers from './transformers'

export function services (dependencies, applicationState, document) {
  const FunctionalToggleable = dependencies.stdLib.FunctionalToggleable
  const FunctionalArrayCollection = dependencies.stdLib.FunctionalArrayCollection

  const allServices = {
    document: function () {
      return document
    },
    dom: function (container) {
      return new dependencies.uiFramework.Dom.Dom(container.document)
    },
    selectorList: function () {
      return []
    },

    store (container) {
      const store = makeStoreObject({
        deepSet: container.lodash.set,
        deepHas: container.lodash.has,
      })
      return new container.vuex.Store(store)
    },

    settingsValues (container) {
      if (!container.state.settingsUi) {
        return {}
      }
      return container.state.settingsUi.values || {}
    },

    state: function (container) {
      return container['APP_STATE']
    },
    config: function (container) {
      return container['APP_STATE'].config || {}
    },
    availabilityEditorStateToggleable: function (container) {
      return new FunctionalToggleable((newVisibility) => {
        container.store.commit('ui/setAvailabilityModalVisibility', newVisibility)
      }, () => {
        return container.store.state.ui.availabilityModalVisible
      })
    },
    bookingEditorState: function (container) {
      return new FunctionalToggleable((newVisibility) => {
        container.store.commit('ui/setBookingModalVisibility', newVisibility)
      }, () => {
        return container.store.state.ui.bookingModalVisible
      })
    },
    serviceEditorState: function (container) {
      return new FunctionalToggleable((newVisibility) => {
        container.store.commit('set', {
          key: 'services.isModalVisible',
          value: newVisibility
        })
      }, () => {
        return container.store.state.services.isModalVisible
      })
    },
    staffMemberEditorState: function (container) {
      return new FunctionalToggleable((newVisibility) => {
        container.store.commit('set', {
          key: 'staffMembers.isModalVisible',
          value: newVisibility
        })
      }, () => {
        return container.store.state.staffMembers.isModalVisible
      })
    },
    sessionEditorState (container) {
      return new FunctionalToggleable((newVisibility) => {
        container.store.commit('set', {
          key: 'services.isSessionEditorState',
          value: newVisibility
        })
      }, () => {
        return container.store.state.services.isSessionEditorState
      })
    },

    staffMembersEntitiesCollection (container) {
      const store = container.store
      return new FunctionalArrayCollection(() => store.state.staffMembers.list, (value) => {
        store.commit('set', {
          key: 'staffMembers.list',
          value
        })
      }, item => item.id)
    },

    servicesEntitiesCollection (container) {
      const store = container.store
      return new FunctionalArrayCollection(() => store.state.services.list, (value) => {
        store.commit('set', {
          key: 'services.list',
          value
        })
      }, item => item.id)
    },

    availabilitiesCollection (container) {
      const store = container.store

      return new FunctionalArrayCollection(() => {
        return store.state.app.availabilities.rules
      }, (newValue) => {
        store.commit('setNewAvailabilities', newValue)
      }, (item) => {
        return item.id
      })
    },
    bookingsCollection (container) {
      const store = container.store

      return new FunctionalArrayCollection(() => {
        return store.state.app.bookings
      }, (newValue) => {
        store.commit('setNewBookings', newValue)
      }, (item) => {
        return item.id
      })
    },

    'bookingStatusesColors' (container) {
      return container.config.bookingStatusesColors
    },

    'defaultCalendarView' (container) {
      const availableViews = {
        day: 'agendaDay',
        week: 'agendaWeek',
        month: 'month'
      }
      return availableViews[container.config.defaultCalendarView] || 'agendaWeek'
    },

    'weekStartsOn' (container) {
      return container.config.weekStartsOn || 'sunday'
    },

    'weekStartsOnIndex' (container) {
      return {
        'sunday': 0,
        'monday': 1,
        'tuesday': 2,
        'wednesday': 3,
        'thursday': 4,
        'friday': 5,
        'saturday': 6
      }[container.weekStartsOn]
    },

    components: function (container) {
      let components = {}
      /*
       * Get all registered components and provide them to
       * Vue root components only if their name is not started
       * with '_' or 'abstract'
       */
      Object.keys(registeredComponents).filter(key => {
        return key[0] !== '_' && key.indexOf('abstract') === -1
      }).map(key => {
        components[key] = container[key]
      })
      return components
    }
  }
  const registeredComponents = components(dependencies, applicationState)
  const registeredApi = api(dependencies, applicationState)
  const registeredLibs = libs(dependencies, applicationState)
  const registeredTransformers = transformers(dependencies, applicationState)

  return {
    ...allServices,
    ...registeredComponents,
    ...registeredApi,
    ...registeredLibs,
    ...registeredTransformers
  }
}
