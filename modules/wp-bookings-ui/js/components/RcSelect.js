export default function CfRcSelect (VueSelect) {
  return VueSelect.extend({
    props: {
      transition: {
        default: 'empty'
      },

      clearable: {
        default: false
      },
    },
    computed: {
      showClearButton() {
        return !this.multiple && this.clearable && !this.open && this.mutableValue
      }
    },
    methods: {
      /**
       * Select a given option.
       * @param  {Object|String} option
       * @return {void}
       */
      select(option) {
        if (!this.isOptionSelected(option)) {
          if (this.taggable && !this.optionExists(option)) {
            option = this.createOption(option)
          }
          if (this.multiple && !this.mutableValue) {
            this.mutableValue = [option]
          } else if (this.multiple) {
            this.mutableValue.push(option)
          } else {
            this.mutableValue = option
          }
        }
        this.onAfterSelect(option)
      },
    }
  })
}