export default function () {
  return {
    computed: {
      /**
       * Proxy for model. Allows changing model value from inner components
       * without additional methods.
       *
       * @since [*next-version*]
       */
      valueProxy: {
        get () {
          return this.value
        },
        set (value) {
          this.$emit('input', value)
        }
      }
    }
  }
}