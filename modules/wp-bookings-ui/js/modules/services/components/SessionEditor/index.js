import template from './template.html'
import { guessUnit } from '../../libs/sessionHelpers'
import ValidationResult from '../../../../libs/validation/ValidationResult'

export default function (AbstractEntityModalEditor, { mapState }) {
  return AbstractEntityModalEditor.extend({
    ...template,
    inject: {
      _: {
        from: 'translate'
      },

      'inline-editor': 'inline-editor',

      'vueselect': 'vueselect',

      modalState: {
        from: 'sessionEditorState'
      },

      'config': 'config',

      'validatorFactory': 'validatorFactory'
    },
    data () {
      return {
        /**
         * Default session model. Used as form data
         * for the new session form.
         */
        model: {
          id: null,
          type: "fixed_duration",
          label: "",
          data: {
            duration: null,
            resources: []
          },
          price: null
        },

        duration: null,

        timeUnits: [{
          title: this._('mins'),
          seconds: 60,
        },{
          title: this._('hours'),
          seconds: 60 * 60,
        }, {
          title: this._('days'),
          seconds: 60 * 60 * 24
        }, {
          title: this._('weeks'),
          seconds: 60 * 60 * 24 * 7
        }],

        sessionTimeUnit: 60,

        rule: [{
          field: 'price',
          rule: 'min_value',
          value: 1
        }, {
          field: 'data.duration',
          rule: 'min_value',
          value: 1
        }],

        lastValidationResult: new ValidationResult(),
      }
    },
    props: {
      /**
       * Collection of availabilities.
       *
       * @since [next-version]
       *
       * @var {FunctionalCollection} entitiesCollection
       */
      entitiesCollection: {}
    },
    computed: {
      ...mapState('services', {
        entityModel: 'oneSession'
      }),
    },
    mounted () {
      this.$on('seed', () => {
        const guessedUnit = guessUnit(this.model.data.duration) || 0
        this.sessionTimeUnit = this.timeUnits[guessedUnit].seconds
        this.duration = Number(this.model.data.duration) / this.sessionTimeUnit || null

        this.lastValidationResult = new ValidationResult()
      })
    },
    methods: {
      /**
       * Validate current session and if everything is fine, save it.
       */
      saveSession () {
        this.model.data.duration = this.duration * this.sessionTimeUnit
        this.validatorFactory.make(this.rule).validate(this.model).then(result => {
          this.lastValidationResult = result
          if (!this.lastValidationResult.valid) {
            return
          }
          this.saveItem()
        })
      },

      /**
       * Is entity can be updated or deleted.
       *
       * @return {bool}
       */
      entityCanBeModified () {
        return true
      },
    },
    components: {
      'inline-editor': 'inline-editor',
      'vueselect': 'vueselect',
    }
  })
}
