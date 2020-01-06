import template from './template.html'
import GetResourceCapable from './../../mixins/GetResourceCapable'

export default function CfSessions ({ mapState, mapMutations }, FunctionalArrayCollection) {
  return {
    ...template,
    mixins: [ GetResourceCapable ],
    inject: {
      '_': {
        from: 'translate'
      },

      /**
       * Website configuration.
       *
       * @since [*next-version*]
       */
      'config': 'config',

      'orderable-repeater': 'orderable-repeater',
      'humanizeDuration': 'humanizeDuration',
      'session-editor': 'session-editor',

      modalState: {
        from: 'sessionEditorState'
      },
    },
    data () {
      return {
        isTransitioning: false,

        /**
         * Validation error that shown to user
         *
         * @property {string}
         */
        validationError: null,

        /**
         * Store's sessions wrapper.
         *
         * @var {FunctionalArrayCollection}
         */
        sessions: new FunctionalArrayCollection(() => {
          return this.value
        }, (sessions) => {
          this.$emit('input', sessions)
        }, (item) => {
          return item.id
        })
      }
    },
    props: {
      value: {},

      isValidationError: {},

      /**
       * @since [*next-version*]
       *
       * @property {number} sessionLengthLimit Session length limit in seconds.
       */
      sessionLengthLimit: {
        type: Number
      }
    },
    watch: {
      /**
       * Session default watcher that removes validation error
       * when user change session default field's value.
       */
      sessionDefault: {
        deep: true,
        handler () {
          this.validationError = null
          this.limitSessionLength()
        }
      },
      /**
       * Watch for `maxSessionLength` change and apply session length limit if needed.
       *
       * @since [*next-version*]
       */
      maxSessionLength () {
        this.limitSessionLength()
      }
    },
    computed: {
      /**
       * Max possible value of session length (less then day).
       *
       * @since [*next-version*]
       *
       * @var {number}
       */
      maxSessionLength () {
        return Math.floor(this.sessionLengthLimit / this.sessionTimeUnit)
      }
    },
    methods: {
      ...mapMutations('services', [
        'setSessionEditorState'
      ]),

      sessionsOrder (a,b) {
        return a.data.duration - b.data.duration
      },

      setTransitioning (isTransitioning) {
        this.isTransitioning = isTransitioning
      },

      /**
       * Open the session editor with the given session.
       *
       * @since [*next-version*]
       *
       * @param {object} session
       */
      openSessionEditor (session = {}) {
        this.setSessionEditorState(session)
        this.modalState.setState(true)
      },

      humanize (seconds) {
        return this.humanizeDuration(seconds * 1000, {
          units: ['w', 'd', 'h', 'm'],
          round: true
        })
      },

      /**
       * Apply session length limit if needed.
       *
       * @since [*next-version*]
       */
      limitSessionLength () {
        if (!this.sessionLengthLimit) {
          return
        }
        if (this.sessionDefault.sessionLength > this.maxSessionLength) {
          this.sessionDefault.sessionLength = this.maxSessionLength
        }
      },

      /**
       * Add new session to the session's list
       * and when session added clear session default, so user
       * can keep adding new sessions.
       */
      addSession () {
        this.openSessionEditor()
      },

      editSession (session) {
        this.openSessionEditor(session)
      },

      removeSession (removeFunction, session) {
        if (!confirm(this._('Are you sure you want to delete this session? There is no undo option.'))) {
          return
        }
        removeFunction(session)
      },

      /**
       * Client side validation. Session default fields must be greater
       * than 0 to pass this validation.
       *
       * @param {SessionLength} sessionLength Session length to validate.
       *
       * @return {boolean}
       */
      validate (sessionLength) {
        const hasSameSession = this.sessions.hasItem(sessionLength)
        if (hasSameSession) {
          this.validationError = this.$refs.sessionLength.dataset.uniqnessError
          return false;
        }

        if (Number(sessionLength.sessionLength) <= 0) {
          this.validationError = this.$refs.sessionLength.dataset.validationError
          return false;
        }
        else if (sessionLength.price === null || !sessionLength.price.length) {
          this.validationError = this.$refs.price.dataset.validationError
          return false;
        }

        return true;
      }
    },

    components: {
      'orderable-repeater': 'orderable-repeater',
      'session-editor': 'session-editor'
    }
  }
}
