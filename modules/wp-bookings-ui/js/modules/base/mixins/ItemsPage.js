/**
 * Mixing that adds functionality for managing the list of items.
 *
 * @since [*next-version*]
 *
 * @return {object} The mixin.
 */
export function MfItemsPage (store, { mapActions, mapMutations }, mapStore, itemsKey) {
  return {
    store,
    inject: {
      /**
       * @var {Function} _ The translating function.
       *
       * @since [*next-version*]
       */
      '_': { from: 'translate' },

      /**
       * @var {object} config The configuration object.
       *
       * @since [*next-version*]
       */
      'config': 'config',

      /**
       * @var {Function} apiErrorHandlerFactory API Errors Handler factory function.
       *
       * @since [*next-version*]
       */
      'apiErrorHandlerFactory': 'apiErrorHandlerFactory',

      /**
       * @var {NotificationsCenter} notificationsCenter Notifications center for displaying notifications in UI.
       *
       * @since [*next-version*]
       */
      'notificationsCenter': 'notificationsCenter',
      /**
       * @var {FunctionalArrayCollection} entitiesCollection Staff members entities.
       *
       * @since [*next-version]
       */
      'entitiesCollection': { from: `${itemsKey}EntitiesCollection` }
    },
    /**
     * Check object for required properties when it is created.
     *
     * @since [*next-version*]
     */
    created () {
      if (!this.api) {
        throw new Error('`this.api` is not injected')
      }
      if (!this.openEditor) {
        throw new Error('`this.openEditor()` method is not implemented')
      }
    },

    data () {
      return {
        /**
         * @var {string} search The search string.
         *
         * @since [*next-version]
         */
        search: '',

        /**
         * @var {boolean} isInitialFetchResults Whether the items list is the result of the first fetching.
         *
         * @since [*next-version]
         */
        isInitialFetchResults: false,

        /**
         * @var {boolean} isPaginated Whether the items list is paginated.
         *
         * @since [*next-version]
         */
        isPaginated: false,

        /**
         * @var {ApiErrorHandler} apiErrorHandler Handles error responses for the services API.
         *
         * @since [*next-version*]
         */
        apiErrorHandler: this.apiErrorHandlerFactory((error) => {
          this.isLoadingList = false
          this.notificationsCenter.error(error)
        }),
      }
    },

    computed: {
      /**
       * Map items list and loading indicator from the items store.
       */
      ...mapStore(itemsKey, [
        'list',
        'isLoadingList'
      ])
    },

    /**
     * Fetch items when the component is mounted.
     *
     * @since [*next-version*]
     */
    mounted () {
      this.fetchItems()
    },

    methods: {
      /**
       * Mapping reading and deleting actions from the store.
       *
       * @since [*next-version*]
       */
      ...mapActions(itemsKey, {
        dispatchDelete: 'delete',
        dispatchFetch: 'fetch'
      }),

      /**
       * Delete the item from the server using the action.
       *
       * @since [*nex-version*]
       *
       * @return {Promise<any>} Promise-based delete response.
       */
      deleteItem (model) {
        if (!confirm(this._('Are you sure you want to delete this item? There is no undo option.'))) {
          return
        }
        this.$set(model, 'isSaving', true)
        return this.dispatchDelete({ api: this.api, model }).then(() => {
          this.$set(model, 'isSaving', false)

          if (this.entitiesCollection.hasItem(model) && !this.isPaginated) {
            this.entitiesCollection.removeItem(model)
          }
        })
      },

      /**
       * Fetch the list of the items.
       *
       * @since [*next-version*]
       *
       * @return {Promise<any>} Promise-based fetch response.
       */
      fetchItems () {
        const params = Object.assign({}, {
          api: this.api,
          params: this.buildParams(),
        }, this.getDispatchFetchParams ? this.getDispatchFetchParams() : {})

        return this.dispatchFetch(params).then(() => {
          this.isInitialFetchResults = !this.search
        }).catch(error => this.apiErrorHandler.handle(error))
      },

      /**
       * Get params for fetching the list of items.
       *
       * @since [*next-version*]
       *
       * @return {object}
       */
      buildParams () {
        const params = {}
        if (this.search) {
          params['s'] = this.search
        }
        return params
      }
    }
  }
}
