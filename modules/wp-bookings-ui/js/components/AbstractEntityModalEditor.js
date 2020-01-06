/**
 * Abstraction for entities modal editor. This is needed because all
 * entities modal editors have common things under the hood:
 *
 * - can remove entity from it's store after confirmation
 * - edit or create model based on data from store
 *
 * Entity - is instance of something that can be edited or created. For example,
 * it can be instance of `Car` that described by brand name and count of wheels (probably 4).
 *
 * @param Vue
 * @return {*}
 * @constructor
 */
export default function CfAbstractEntityModalEditor (Vue) {
  return Vue.extend({
    /**
     * This is copy of empty data model. It is used to restore
     * model to default state when editor is closed by user.
     *
     * @var {{}}
     */
    $model: {},

    inject: [
      'equal'
    ],

    data () {
      return {
        /**
         * The seeding lock for preventing model's watching during model's fields seeding.
         *
         * Some business logic can be written in watchers. So in order to not
         * trigger that logic when we are seeding initial model fields (for example when we
         * open model for editing or creating new model) this flag can be used.
         *
         * @var {boolean}
         */
        seedLock: false,

        /**
         * Is remove confirmation visible
         *
         * @var {boolean}
         */
        removeConfirming: false,

        /**
         * Is close confirmation visible
         *
         * @var {boolean}
         */
        closeConfirming: false,

        /**
         * Is cancel confirmation visible
         *
         * @var {boolean}
         */
        cancelConfirming: false,

        /**
         * Model's state that was opened for creating OR for editing.
         * It used for determining model's changes when user edits the model.
         *
         * @var {Object}
         */
        mountedModel: {},

        /**
         * Model's skeleton. Represents an empty model.
         *
         * MUST be provided by the consumer.
         *
         * @var {Object}
         */
        model: null,

        /**
         * Modal's state (is modal is visible).
         *
         * MUST be provided by the consumer.
         *
         * @var {FunctionalToggleable}
         */
        // modalState: null,

        /**
         * Entities collection in their source of truth (in the store, for example).
         * For example, if Car is edited, entities collection is the collection of Cars.
         *
         * MUST be provided by the consumer.
         *
         * @var {FunctionalCollection}
         */
        // entitiesCollection: null,

        /**
         * Entity model in its source of truth (in the store, for example).
         *
         * MUST be provided by the consumer.
         *
         * @var {Object}
         */
        // entityModel: null,
      }
    },

    computed: {
      /**
       * Is modal with fields visible.
       *
       * @var {boolean}
       */
      modalIsVisible () {
        return this.modalState.isOn()
      },

      /**
       * Are we in the double confirmation mode
       *
       * @var {boolean}
       */
      isDoubleConfirming () {
        return this.removeConfirming || this.closeConfirming || this.cancelConfirming
      },

      /**
       * Check model is changed.
       *
       * @return {boolean}
       */
      isModelChanged () {
        return !this.equal(this.model, this.mountedModel)
      },
    },

    watch: {
      /**
       * Watch for modal opening and if modal is opened, seed
       * model fields with data that already in the store.
       */
      modalIsVisible () {
        this.seedModelFields()
      }
    },

    /**
     * Do initialization stuff on component mount. It will check that everything
     * is passed correctly and child component will work without any problems.
     *
     * Also here we copying initial model to the this.$model field, so we will be able to:
     *
     * - restore initial empty state when we need it
     * - create entities with seeded fields
     */
    mounted () {
      if (!this.model) {
        throw new Error('Model empty state is not provided. It should be available via this.model and describe entity')
      }

      if (!this.modalState) {
        throw new Error('Modal state toggle is not provided. It should be available via this.modalState')
      }

      if (!this.entityModel) {
        throw new Error('Entity model is not provided. It should be available via this.entityModel')
      }

      if (!this.entitiesCollection) {
        throw new Error('Entities collection is not provided. It should be available via this.entitiesCollection ' +
          'and has type of FunctionalCollection')
      }

      /*
       * Store initial empty model's state
       */
      this.$model = Object.assign({}, this.model)
    },

    methods: {
      /**
       * Prepare model for editing. It can be new entity or entity for editing.
       * Model fields seeded by values from Vuex store.
       *
       * This may look complicated, but DON'T PANIC because the answer is 42!
       *
       * Purpose of this method is to prepare model to work with form with all
       * necessary fields. Also here we store initial model's state to check does
       * model changed in future.
       */
      seedModelFields () {
        this.seedLock = true

        let model = Object.assign({}, this.$model, this.entityModel)

        Object.keys(model).map((key) => {
          Vue.set(this.model, key, JSON.parse(JSON.stringify(model[key])))
          Vue.set(this.mountedModel, key, JSON.parse(JSON.stringify(model[key])))
        })

        this.closeAllConfirmation()
        this.$emit('seed')

        this.$nextTick(() => {
          this.seedLock = false
        })
      },

      /**
       * Close all confirmation dialogs.
       */
      closeAllConfirmation () {
        /**
         * Set default confirmation state
         *
         * @type {boolean}
         */
        this.removeConfirming = false
        this.closeConfirming = false
        this.cancelConfirming = false
      },

      /**
       * Show close modal confirmation if editing model
       * was changed.
       */
      closeModal (field = 'closeConfirming') {
        if (this.entityCanBeModified && !this.entityCanBeModified()) {
          this.forceCloseModal()
          return
        }

        if (this.isModelChanged) {
          this[field] = true
          return
        }

        this.forceCloseModal()
      },

      /**
       * Close modal from footer.
       */
      cancelModal () {
        this.closeModal('cancelConfirming')
      },

      /**
       * Close modal without data saving
       */
      forceCloseModal () {
        this.modalState.setState(false)
      },

      /**
       * Continue editing and don't close the modal
       */
      continueEditing () {
        this.closeConfirming = false
      },

      /**
       * Save editing item. If items is new it will update
       * all items in store by adding new item.
       *
       * If item is editing it will remove and add new item to the store.
       *
       * @todo: review
       */
      saveItem (savingModel = null) {
        let model = Object.assign({}, savingModel || this.model)

        if (!model.id) {
          let id = '_' + Math.random().toString(36).substring(7)
          this.entitiesCollection.addItem(Object.assign({}, model, { id }))

          this.forceCloseModal()
          return
        }

        /**
         * Update model
         */
        if (this.entitiesCollection.hasItem(model)) {
          this.entitiesCollection.removeItem(model)
          this.entitiesCollection.addItem(model)
        }

        this.forceCloseModal()
      },

      /**
       * Remove entity from store and close modal.
       *
       * This happens when user confirmed that he wants
       * to delete entity item.
       */
      deleteItem () {
        this.entitiesCollection.removeItem(this.model)
        this.forceCloseModal()
      },
    }
  })
}
