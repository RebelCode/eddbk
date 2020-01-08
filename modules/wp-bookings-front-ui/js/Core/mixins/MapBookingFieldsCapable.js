export default function (mapStore, bookingDataMap) {
  return {
    computed: {
      ...mapStore('booking', bookingDataMap)
    }
  }
}