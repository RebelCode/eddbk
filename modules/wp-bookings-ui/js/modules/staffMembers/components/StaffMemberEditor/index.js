import template from './template.html'
import ValidationResult from '../../../../libs/validation/ValidationResult'

/**
 * The staff member modal editor.
 *
 * @param AbstractEntityModalEditor
 * @param mapState
 * @param mapMutations
 * @param mapActions
 *
 * @return {Component}
 */
export function CfStaffMemberEditor (AbstractEntityModalEditor, { mapState, mapMutations, mapActions }) {
  return AbstractEntityModalEditor.extend({
    ...template,
    inject: {
      /**
       * @var {FunctionalToggleable} modalState Modal state injected from elsewhere.
       *
       * @since [*next-version]
       */
      modalState: {
        from: 'staffMemberEditorState'
      },

      /**
       * @var {StaffMembersApi} api The staff members API client.
       *
       * @since [*next-version]
       */
      'api': {
        from: 'staffMembersApi'
      },

      /**
       * @var {Function} _ The translating function.
       *
       * @since [*next-version*]
       */
      '_': {
        from: 'translate'
      },

      /**
       * @var {object} config Application's configuration.
       *
       * @since [*next-version*]
       */
      'config': 'config',

      /**
       * @var {AvailabilityHelpers} availabilityHelpers Set of helper methods for availabilities.
       *
       * @since [*next-version*]
       */
      'availabilityHelpers': 'availabilityHelpers',

      /**
       * @var {ValidatorFactory} validatorFactory Factory for making validators based on array configs.
       *
       * @since [*next-version*]
       */
      'validatorFactory': 'validatorFactory',

      /**
       * @var {FunctionalToggleable} availabilityEditorStateToggleable Availability editor state.
       *
       * @since [*next-version*]
       */
      'availabilityEditorStateToggleable': 'availabilityEditorStateToggleable',

      /**
       * API Errors Handler factory function.
       *
       * @since [*next-version*]
       *
       * @var {Function}
       */
      'apiErrorHandlerFactory': 'apiErrorHandlerFactory',

      /**
       * @var {Component} availabilities The component for managing availabilities.
       *
       * @since [*next-version]
       */
      'availabilities': 'availabilities',

      /**
       * @var {Component} tabs The component for creating switchable tabs.
       *
       * @since [*next-version]
       */
      'tabs': 'tabs',

      /**
       * @var {Component} tab The tab.
       *
       * @since [*next-version]
       */
      'tab': 'tab',

      /**
       * @var {Component} modal Modal component.
       *
       * @since [*next-version]
       */
      'modal': 'modal',

      /**
       * @var {Component} v-image-selector Component for selecting images from the gallery.
       *
       * @since [*next-version]
       */
      'v-image-selector': 'v-image-selector',

      /**
       * @var {Component} timezone-select Component for selecting timezones.
       *
       * @since [*next-version]
       */
      'timezone-select': 'timezone-select',

      /**
       * @var {FunctionalArrayCollection} entitiesCollection Staff members entities.
       *
       * @since [*next-version]
       */
      'entitiesCollection': { from: 'staffMembersEntitiesCollection' }
    },
    data () {
      return {
        errorMessage: null,

        /**
         * @var {boolean} isSaving Whether the staff member that is being edited is saving.
         *
         * @since [*next-version*]
         */
        isSaving: false,

        /**
         * @var {number|sting} activeTab ID of the active tab.
         *
         * @since [*next-version*]
         */
        activeTab: 0,

        /**
         * @var {object} tabsConfig Tabs configuration.
         *
         * @since [*next-version*]
         */
        tabsConfig: {
          switcherClass: 'horizontal-tabs',
          switcherItemClass: 'horizontal-tabs__item',
          switcherActiveItemClass: '_active',
          tabsClass: 'tabs-content'
        },

        /**
         * @var {object[]} rules Validation rules for a staff member.
         *
         * @since [*next-version*]
         */
        rules: [{
          field: 'name',
          rule: 'required'
        }],

        /**
         * @var {ValidationResult} lastValidationResult The last result of the validation.
         *
         * @since [*next-version*]
         */
        lastValidationResult: new ValidationResult(),

        /**
         * @var {object} model A staff member model.
         *
         * @since [*next-version*]
         */
        model: {
          id: null,
          name: '',
          data: {
            imageId: null,
            imageSrc: null,
          },
          availability: {
            rules: [],
            timezone: this.config.timezone
          }
        },

        /**
         * @var {ApiErrorHandler} staffMembersApiErrorHandler Handles error responses for the staff members API.
         *
         * @since [*next-version*]
         */
        staffMembersApiErrorHandler: this.apiErrorHandlerFactory((error) => {
          this.setSaving(false)
          this.errorMessage = error
        }),
      }
    },
    computed: {
      ...mapState('staffMembers', {
        entityModel: 'one',
      }),

      /**
       * Staff member's image model.
       *
       * @since [*next-version*]
       */
      staffMemberImage: {
        get () {
          return {
            id: this.model.data.imageId,
            url: this.model.data.imageSrc,
          }
        },
        set (value) {
          this.model.data.imageId = value.id
          this.model.data.imageSrc = value.url
        }
      },
    },
    watch: {
      /**
       * Watch for model changes and remove error message once it is changed.
       *
       * @since [*next-version*]
       */
      model: {
        deep: true,
        handler () {
          this.errorMessage = null
        }
      },
    },
    mounted () {
      /*
       * Switch tab to the first on fields seed.
       */
      this.$on('seed', () => {
        this.activeTab = 0
      })
    },
    methods: {
      ...mapActions('staffMembers', {
        dispatchCreate: 'create',
        dispatchUpdate: 'update'
      }),

      /**
       * Save the staff member that is being edited.
       *
       * @since [*next-version*]
       *
       * @return {Promise<T>} The promise that holds server's response data.
       */
      save () {
        const validator = this.validatorFactory.make(this.rules)
        return validator.validate(this.model).then(result => {
          this.lastValidationResult = result
          if (this.lastValidationResult.valid) {
            return true
          }
        }).then(isValid => {
          if (!isValid) {
            return
          }
          const dispatchSaveMethod = this.model.id ? 'dispatchUpdate' : 'dispatchCreate'

          this.setSaving(true)
          const model = Object.assign({}, this.model)

          return this[dispatchSaveMethod]({api: this.api, model}).then(item => {
            this.setSaving(false)
            const storedModel = item
            if (this.entitiesCollection.hasItem(storedModel)) {
              this.entitiesCollection.removeItem(storedModel)
            }
            this.entitiesCollection.addItem(storedModel)

            this.forceCloseModal()
          })
        }).catch(error => this.staffMembersApiErrorHandler.handle(error))
      },

      /**
       * Set saving indicator.
       *
       * @since [*next-version*]
       *
       * @param {string|boolean} status Status name or `false` to set all indicators to false.
       * @param {boolean} value Saving indicator for status.
       */
      setSaving (value) {
        this.isSaving = value
      }
    },
    components: {
      tabs: 'tabs',
      tab: 'tab',
      modal: 'modal',
      'availabilities': 'availabilities',
      'v-image-selector': 'v-image-selector',
      'timezone-select': 'timezone-select',
    }
  })
}
