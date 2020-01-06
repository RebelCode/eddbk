<script>
  import { ElementMixin } from 'vue-slicksort'

  export default {
    mixins: [ElementMixin],
    props: {
      wrapper: {
        type: String,
        default: 'div'
      }
    },
    render (h) {
      return h(this.wrapper, this.$slots.default)
    }
  }
</script>
