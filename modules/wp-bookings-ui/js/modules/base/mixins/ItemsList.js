/**
 * The services list component.
 *
 * @since [*next-version*]
 *
 * @param mapStore
 *
 * @return {*}
 */
export function MfItemsList (mapStore, itemsKey) {
  return {
    inject: {
      /**
       * @var {Function} _ The translating function.
       *
       * @since [*next-version*]
       */
      '_': { from: 'translate' }
    },

    computed: {
      /**
       * Map items list and loading indicator from the items store.
       */
      ...mapStore(itemsKey, [
        'list',
        'isLoadingList'
      ]),

      /**
       * @var {object<id: number>[]} orderedList List of items ordered in desc order.
       *
       * @since [*next-version*]
       */
      orderedList () {
        return this.list.slice().sort((a, b) => Number(b.id) - Number(a.id))
      }
    },

    props: {
      /**
       * @property {boolean} isInitialFetchResults Whether the items list is the result of the first fetching.
       *
       * @since [*next-version]
       */
      isInitialFetchResults: {
        default: false
      },
    },
  }
}
