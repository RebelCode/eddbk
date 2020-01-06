export function CfSettingsApplication (store, { mapActions }, mapStore, settingsValues = {}) {
  /**
   * Key of settings fields in store.
   *
   * @since [*next-version*]
   *
   * @type {string}
   */
  const STORE_SETTINGS_KEY = 'settings'

  return {
    store,

    inject: {
      /**
       * @since [*next-version*]
       *
       * @property {UpdateCapable} settingsApi Update capable API for settings.
       */
      settingsApi: 'settingsApi',

      /**
       * @since [*next-version*]
       *
       * @property {{timezone: string}} config Configuration of application.
       */
      config: 'config',

      /**
       * @since [*next-version*]
       *
       * @property {{settingsUi: object}} state State of application.
       */
      state: 'state',

      /**
       * @since [*next-version*]
       *
       * @property {TranslateFunction} _ Function for i18n strings.
       */
      '_': {
        from: 'translate'
      },

      /**
       * @since [*next-version*]
       *
       * @property {object} switcher Component for selecting value by switching button.
       */
      switcher: 'switcher',

      /**
       * @since [*next-version*]
       *
       * @property {object} tabs Tabs component.
       */
      tabs: 'tabs',

      /**
       * @since [*next-version*]
       *
       * @property {object} tab Tab component.
       */
      tab: 'tab',

      /**
       * @since [*next-version*]
       *
       * @property {VueComponent} color-picker Color picker component.
       */
      'color-picker': 'color-picker',

      /**
       * @since [*next-version*]
       *
       * @property {VueComponent} wizard-editor Wizard editor component.
       */
      'wizard-editor': 'wizard-editor',

      /**
       * Notifications center for displaying notifications in UI.
       *
       * @since [*next-version*]
       *
       * @var {NotificationsCenter}
       */
      'notificationsCenter': 'notificationsCenter'
    },

    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {boolean} isSaving Are settings is storing at the moment.
         */
        isSaving: false,

        /**
         * @since [*next-version*]
         *
         * @property {object} initialSettingsValues Initial value of settings.
         */
        initialSettingsValues: {},

        /**
         * @since [*next-version*]
         *
         * @property {number|string} activeTab Active tab ID.
         */
        activeTab: 0
      }
    },

    /**
     * Set initial values and update store structure when component is created.
     *
     * @since [*next-version*]
     */
    created () {
      this.initialSettingsValues = Object.assign({}, JSON.parse(JSON.stringify(settingsValues)))
      this._hydrateStore(settingsValues)
    },

    computed: {
      /**
       * Settings fields in store.
       *
       * @since [*next-version*]
       */
      ...mapStore(STORE_SETTINGS_KEY, Object.keys(settingsValues)),

      /**
       * @since [*next-version*]
       *
       * @property {object} settingsState All settings in store.
       */
      settingsState () {
        return this.$store.state[STORE_SETTINGS_KEY]
      },

      /**
       * @since [*next-version*]
       *
       * @property {object} settingsConfig Configuration of settings UI.
       */
      settingsConfig () {
        return this.state.settingsUi
      },
    },

    props: {
      /**
       * @since [*next-version*]
       *
       * @property {string} successMessage Message on successful settings update.
       */
      successMessage: {},

      /**
       * @since [*next-version*]
       *
       * @property {string} errorMessage Message on error durings settings update.
       */
      errorMessage: {},
    },

    methods: {
      /**
       * Store action for updating settings.
       *
       * @since [*next-version*]
       *
       * @method {Function} updateSettings
       */
      ...mapActions(STORE_SETTINGS_KEY, [
        'updateSettings'
      ]),

      /**
       * Submit state and update settings.
       *
       * @since [*next-version*]
       *
       * @return {Promise<any>} Settings updating promise.
       */
      submit () {
        this.isSaving = true
        return this.updateSettings({api: this.settingsApi, data: this.settingsState})
          .then(() => {
            this.initialSettingsValues = JSON.parse(JSON.stringify(this.settingsState))
            this.isSaving = false
            this.notificationsCenter.show(this.successMessage)
          })
          .catch(() => {
            this.isSaving = false
            this.notificationsCenter.error(this.errorMessage)
          })
      },

      /**
       * Revert changes applied to settings.
       *
       * @since [*next-version*]
       */
      revertChanges () {
        for (let fieldName in this.initialSettingsValues) {
          this[fieldName] = JSON.parse(JSON.stringify(this.initialSettingsValues[fieldName]))
        }
      },

      /**
       * Set structure of store for settings.
       *
       * @since [*next-version*]
       *
       * @param {object} fields Map of settings fields to their values.
       */
      _hydrateStore (fields) {
        this.$store.replaceState(Object.assign({}, this.$store.state, {
          [STORE_SETTINGS_KEY]: fields
        }))
      }
    },

    components: {
      'color-picker': 'color-picker',
      'wizard-editor': 'wizard-editor',
      switcher: 'switcher',
      tabs: 'tabs',
      tab: 'tab'
    }
  }
}
