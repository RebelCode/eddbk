<template>
    <div class="editable-input">
        <input type="text"
               class="editable-input__field"
               :placeholder="defaultValue"
               v-model="newValue"
               :readonly="!isEditing"
               @click="openEdit"
               @blur="saveEdit"
               @keyup.enter="saveEdit"
               @keyup.esc="cancelEdit"
               ref="input"
        >
    </div>
</template>

<script>
  /**
   * Component for making text editable on click.
   *
   * @since [*next-version*]
   */
  export default {
    data () {
      return {
        newValue: null,
        isEditing: false,
        isSeeding: false
      }
    },
    inject: {
      'textFormatter': 'textFormatter'
    },
    mounted () {
      this.newValue = this.preparePreview(this.value)
    },
    props: {
      /**
       * Model of component.
       *
       * @since [*next-version*]
       *
       * @property {string} value
       */
      value: {},

      /**
       * Values that should be used by default, when original value becomes empty.
       *
       * @since [*next-version*]
       *
       * @property {string?} defaultValue
       */
      defaultValue: {},

      /**
       * Values that should be used instead of placeholders in resulting preview string.
       *
       * @since [*next-version*]
       *
       * @property {object?} values
       */
      values: {}
    },
    watch: {
      value (val) {
        if (this.isSeeding) {
          return
        }
        this.newValue = this.preparePreview(val)
      }
    },
    computed: {
      valueProxy: {
        get () {
          return this.value
        },
        set (value) {
          this.isSeeding = true
          this.$emit('input', value)
          this.$nextTick(() => {
            this.isSeeding = false
          })
        }
      }
    },
    methods: {
      /**
       * Enable edit mode of the input.
       *
       * @since [*next-version*]
       *
       * @method openEdit
       */
      openEdit () {
        if (this.isEditing) {
          return
        }
        this.newValue = this.valueProxy
        this.isEditing = true
        this.$nextTick(() => {
          this.$refs.input.focus()
          this.$refs.input.select()
        })
      },

      /**
       * Cancel editing without saving result.
       *
       * @since [*next-version*]
       *
       * @method cancelEdit
       */
      cancelEdit () {
        if (!this.isEditing) {
          return
        }
        this.newValue = this.preparePreview(this.valueProxy)
        this.isEditing = false
      },

      /**
       * Save editing result.
       *
       * @since [*next-version*]
       *
       * @method saveEdit
       */
      saveEdit () {
        if (!this.isEditing) {
          return
        }
        const newValue = this.newValue || this.defaultValue
        this.valueProxy = newValue
        this.newValue = this.preparePreview(newValue)
        this.isEditing = false
      },

      /**
       * Prepares preview after value is saved. It puts value placeholder on `%s` places.
       *
       * @since [*next-version*]
       *
       * @method preparePreview
       */
      preparePreview (value) {
        return this.textFormatter(value, Object.values(this.values || {}))
      }
    }
  }
</script>
