<template>
    <div class="wizard-editor">
        <div class="wizard-editor__header">
            <editable-input class="wizard-editor__title"
                            v-model="overrides.general.title"
                            :defaultValue="defaults.general.title"
            />
            <div class="wizard-steps">
                <div class="wizard-steps__item"
                     v-for="availableScreen of allScreens"
                     :style="availableScreen === screen ? wizardStyle : {}"
                     :class="{'wizard-steps__item--active': screen === availableScreen}"
                     @click="screen = availableScreen"
                ></div>
            </div>
        </div>
        <div class="wizard-editor__body">
            <template v-if="screen === 'service'">
                <div class="form-row form-row--wizard">
                    <div class="form-row__label">
                        <editable-input v-model="overrides.fields.service.title" :defaultValue="defaults.fields.service.title"/>
                    </div>
                    <div class="form-row__input">
                        <div class="wizard-editor__input">
                            <editable-input v-model="overrides.fields.service.placeholder" :defaultValue="defaults.fields.service.placeholder"/>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="screen === 'session'">
                <div class="wizard-info wizard-info--wider">
                    <editable-input style="display: inline-block" v-model="overrides.preview.booking" :defaultValue="defaults.preview.booking"/>
                    <span class="wizard-editor__word" style="width: 50px"></span>
                    <span class="wizard-editor__word" style="width: 60px"></span>
                    <span class="wizard-editor__word" style="width: 30px"></span>
                    <hr>
                    <div class="form-row form-row--wizard">
                        <div class="form-row__label">
                            <editable-input v-model="overrides.fields.timezone.title" :defaultValue="defaults.fields.timezone.title"/>
                        </div>
                        <div class="form-row__input">
                            <div class="wizard-editor__input wizard-editor__input--placeholder">
                                <div class="wizard-editor__solid"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <v-sortable-list v-model="wizardFields"
                                 :lock-axis="'y'"
                                 style="margin-bottom: 1.5rem"
                >
                    <v-sortable-item class="form-row form-row--wizard" :index="i" :key="i" v-for="(field, i) of wizardFields">
                        <span class="drag-icon dashicons dashicons-menu"></span>
                        <div class="form-row__label">
                            <editable-input v-model="overrides.fields[field].title" :defaultValue="defaults.fields[field].title"/>
                        </div>
                        <div class="form-row__input">
                            <div class="wizard-editor__input wizard-editor__input--placeholder">
                                <div class="wizard-editor__solid"></div>
                            </div>
                        </div>
                    </v-sortable-item>
                </v-sortable-list>
                <div class="form-row form-row--wizard">
                    <div class="form-row__label">
                        <editable-input v-model="overrides.fields.date.title" :defaultValue="defaults.fields.date.title"/>
                    </div>
                    <div class="form-row__input">
                        <div class="wizard-info">
                            <editable-input v-model="overrides.fields.date.empty" :defaultValue="defaults.fields.date.empty"/>
                        </div>
                        <div class="wizard-editor__input wizard-editor__input--placeholder">
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                        </div>
                    </div>
                </div>
                <div class="form-row form-row--wizard">
                    <div class="form-row__label">
                        <editable-input v-model="overrides.fields.time.title" :defaultValue="defaults.fields.time.title"/>
                    </div>
                    <div class="form-row__input">
                        <div class="info">
                            <editable-input v-model="overrides.fields.time.placeholder" :defaultValue="defaults.fields.time.placeholder"/>
                        </div>
                        <div class="wizard-editor__input wizard-editor__input--placeholder">
                            <div class="wizard-editor__solid"></div>
                        </div>
                        <div class="wizard-editor__input wizard-editor__input--placeholder">
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                            <div class="wizard-editor__solid"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="screen === 'confirmation'">
                <div class="wizard-info wizard-info--wider">
                    <editable-input
                            v-model="overrides.confirmation.booking"
                            :defaultValue="defaults.confirmation.booking"
                            :values="{service: 'Some Service', duration: '1 hour'}"
                    />
                    <editable-input
                            v-model="overrides.confirmation.with"
                            :defaultValue="defaults.confirmation.with"
                            :values="{name: 'Nick Bell'}"
                    />
                    <editable-input
                            v-model="overrides.confirmation.starting"
                            :defaultValue="defaults.confirmation.starting"
                            :values="{start: '1:00 am on Thursday 11th October 2018'}"
                    />
                    <editable-input
                            v-model="overrides.confirmation.price"
                            :defaultValue="defaults.confirmation.price"
                            :values="{price: '$45.00'}"
                    />
                </div>
                <div class="form-row form-row--wizard">
                    <div class="form-row__label">
                        <editable-input v-model="overrides.fields.notes.title" :defaultValue="defaults.fields.notes.title"/>
                    </div>
                    <div class="form-row__input">
                        <div class="wizard-editor__input" style="min-height: 50px">
                            <editable-input v-model="overrides.fields.notes.placeholder" :defaultValue="defaults.fields.notes.placeholder"/>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div class="wizard-editor__footer">
            <div class="wizard-editor__footer--left">
                <template v-if="screen === 'service'">
                    <editable-input
                            v-model="overrides.preview.price"
                            :defaultValue="defaults.preview.price"
                            :values="{price: '$45.00', duration: '1 hour'}"
                    />
                    <div class="info">
                        <editable-input
                                v-model="overrides.preview.available"
                                :defaultValue="defaults.preview.available"
                        />
                    </div>
                </template>
            </div>
            <div class="wizard-editor__button">
                <editable-input v-model="overrides.general.buttons.back" :defaultValue="defaults.general.buttons.back"/>
            </div>
            <div class="wizard-editor__button wizard-editor__button--primary" :style="wizardStyle">
                <editable-input v-model="overrides.general.buttons.next" :defaultValue="defaults.general.buttons.next" v-if="screen !== 'confirmation'"/>
                <editable-input v-model="overrides.general.buttons.book" :defaultValue="defaults.general.buttons.book" v-else/>
            </div>
        </div>
    </div>
</template>

<script>
  /**
   * Helper function for making plain copies of objects.
   *
   * @param {object} obj Object with observers.
   *
   * @return {object} Copy of the given object without observers.
   */
  const plainCopy = obj => JSON.parse(JSON.stringify(obj))

  /**
   * Component for editing wizard labels.
   *
   * @since [*next-version*]
   */
  export default {
    name: 'WizardEditor',
    inject: {
      /**
       * Component for editing text in inline input.
       *
       * @since [*next-version*]
       *
       * @var {Component} `editable-input`
       */
      'editable-input': 'editable-input',

      'v-sortable-item': 'v-sortable-item',

      'v-sortable-list': 'v-sortable-list',

      /**
       * Get object that represents deep difference between two given objects.
       *
       * @since [*next-version*]
       *
       * @var {Function} deepObjectDifference
       */
      'deepObjectDifference': 'deepObjectDifference',

      /**
       * Deep merge of two objects.
       *
       * @since [*next-version*]
       *
       * @var {Function} defaultsDeep
       */
      'defaultsDeep': 'defaultsDeep',
    },

    /**
     * Seed `overrides` when component is created, but not mounted yet.
     *
     * @since [*next-version*]
     */
    created () {
      this.isSeeding = true
      this.overrides = this.defaultsDeep(plainCopy(this.value), plainCopy(this.defaults))
      this.$nextTick(() => {
        this.isSeeding = false
      })
    },

    data () {
      return {
        /**
         * Current active screen (wizard step).
         *
         * @since [*next-version*]
         */
        screen: 'service',

        /**
         * List of all available screens.
         *
         * @since [*next-version*]
         */
        allScreens: [
          'service', 'session', 'confirmation'
        ],

        isSeeding: false,
        overrides: null,
      }
    },

    props: {
      defaults: {},
      value: {},
      color: {
        type: String,
        default: '#0b92da'
      },
      fieldsOrder: {
        default () {
          return []
        }
      }
    },

    watch: {
      value: {
        deep: true,
        /**
         * Re-seed `overrides` when value is changed from outside.
         *
         * @since [*next-version*]
         *
         * @param value
         */
        handler (value) {
          this.isSeeding = true
          this.overrides = this.defaultsDeep(plainCopy(value), plainCopy(this.defaults))
          this.$nextTick(() => {
            this.isSeeding = false
          })
        }
      },
      overrides: {
        deep: true,
        /**
         * Update component's value when overrides updated.
         *
         * @since [*next-version*]
         */
        handler () {
          if (this.isSeeding) {
            return false
          }
          this.$emit('input', this.deepObjectDifference(plainCopy(this.overrides), plainCopy(this.defaults)))
        }
      }
    },

    computed: {
      wizardFields: {
        get () {
          return this.fieldsOrder
        },
        set (value) {
          this.$emit('update:fieldsOrder', value)
        }
      },
      wizardStyle () {
        return {
          'background-color': this.color
        }
      }
    },

    components: {
      'editable-input': 'editable-input',
      'v-sortable-item': 'v-sortable-item',
      'v-sortable-list': 'v-sortable-list',
    }
  }
</script>
