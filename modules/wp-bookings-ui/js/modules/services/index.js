export { default as store } from './store'

/**
 * Component for the services page.
 *
 * @since [*next-version*]
 *
 * @return {object} Services page component.
 */
export function page (makeItemsPageMixin, store, { mapActions, mapMutations }, mapStore) {
  return {
    mixins: [
      makeItemsPageMixin(store, { mapActions, mapMutations }, mapStore, 'services')
    ],
    inject: {
      /**
       * @var {ServicesApi} api The services API client.
       *
       * @since [*next-version]
       */
      'api': {
        from: 'servicesApi'
      },

      /**
       * @var {StaffMembersApi} staffMembersApi The API client for staff members.
       *
       * @since [*next-version]
       */
      'staffMembersApi': 'staffMembersApi',

      /**
       * @var {FunctionalToggleable} modalState Modal state injected from elsewhere.
       *
       * @since [*next-version]
       */
      modalState: {
        from: 'serviceEditorState'
      },

      /**
       * @var {Component} service-editor The service editor component.
       *
       * @since [*next-version]
       */
      'service-editor': 'service-editor',

      /**
       * @var {Component} service-availability-editor The service availability editor component.
       *
       * @since [*next-version]
       */
      'service-availability-editor': 'service-availability-editor',

      /**
       * @var {Component} services The services list component (display the list of services).
       *
       * @since [*next-version]
       */
      'services': 'services',
    },

    /**
     * Fetch staff members when component is mounted.
     *
     * @since [*next-version*]
     */
    mounted () {
      this.$store.dispatch('staffMembers/fetch', { api: this.staffMembersApi })
    },

    methods: {
      /**
       * Map the service editor visibility mutation from the store.
       *
       * @since [*next-version*]
       */
      ...mapMutations('services', [
        'setServiceEditorState'
      ]),

      /**
       * Map updating action from the store.
       *
       * @since [*next-version*]
       */
      ...mapActions('services', {
        dispatchUpdate: 'update',
      }),

      /**
       * Open service's modal.
       *
       * @since [*next-version*]
       *
       * @param {service} service The service that is being edited.
       */
      openEditor (service = {}) {
        this.modalState.setState(true)
        this.setServiceEditorState(service)
      },

      /**
       * Save the service.
       *
       * @since [*next-version*]
       */
      saveItem (model) {
        this.$set(model, 'isSaving', true)
        this.dispatchUpdate({ api: this.api, model }).then(() => {
          this.$set(model, 'isSaving', false)
        })
      },
    },

    /**
     * Components definitions.
     *
     * @see `inject` property in this component for each component's description.
     */
    components: {
      'services': 'services',
      'service-editor': 'service-editor',
      'service-availability-editor': 'service-availability-editor',
    },
  }
}
