export default function (Repeater) {
  return Repeater.extend({
    props: {
      /**
       * Order function for items.
       *
       * @since [*next-version*]
       *
       * @var {Function|null} order
       */
      order: {
        type: Function
      },
    },
    computed: {
      /**
       * Get items from *injected* collection. Reactive.
       *
       * @since [*next-version*]
       *
       * @return {Array} Collection items as array
       */
      items () {
        let items = Object.values(this.collection.getItems()).slice()
        return this.order ? items.sort(this.order) : items
      }
    },
  })
}
