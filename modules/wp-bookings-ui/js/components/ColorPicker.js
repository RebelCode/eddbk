export function CfColorPicker (clickOutside) {
  return {
    template: '#color-picker-template',

    inject: {
      /**
       * @since [*next-version*]
       *
       * @property {object} core-color-picker Component for selecting color.
       */
      'core-color-picker': 'core-color-picker',

      /**
       * @since [*next-version*]
       *
       * @property {TranslateFunction} _ Function for i18n strings.
       */
      '_': {
        from: 'translate'
      },
    },

    data () {
      return {
        /**
         * @since [*next-version*]
         *
         * @property {{hex: string|null}} color Color object.
         */
        color: {
          hex: null
        },

        /**
         * @since [*next-version*]
         *
         * @property {bool} isPickerOpened Is color picker popup opened.
         */
        isPickerOpened: false
      }
    },

    props: {
      value: {},

      /**
       * Placeholder for the input.
       *
       * @since [*next-version*]
       */
      placeholder: {
        default: 'Select color'
      },

      /**
       * Whether this color can be empty or not.
       *
       * @since [*next-version*]
       *
       * @var {bool} cleanable
       */
      isCleanable: {
        default: false
      }
    },

    created () {
      this.color.hex = this.value
    },

    methods: {
      removeColor () {
        this.$emit('input', null)
      },

      selectColor () {
        this.$emit('input', this.color.hex)
        this.$nextTick(() => {
          this.closePicker()
        })
      },

      openPicker () {
        this.isPickerOpened = true
      },

      closePicker () {
        this.isPickerOpened = false
        this.color = {hex: this.value}
      }
    },

    directives: {
      'click-outside': clickOutside
    },

    components: {
      'core-color-picker': 'core-color-picker'
    }
  }
}