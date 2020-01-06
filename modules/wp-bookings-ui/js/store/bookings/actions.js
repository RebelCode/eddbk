export default {
  fetchBookings ({ commit }, { api, params }) {
    return api.fetch(params).then((response) => {
      commit('setBookings', response.data.items.map(item => {
        return api.bookingReadTransformer.transform(item)
      }))
      commit('setBookingsStatuses', response.data.statuses)
    })
  },

  saveBookingOnBackend ({}, { api, model }) {
    let method = model.id ? 'update' : 'create'
    return api[method](model)
  },

  deleteBookingFromBackend ({}, { api, model }) {
    return api.delete(model)
  }
}