<script>
  import { ContainerMixin } from 'vue-slicksort'
  export default {
    mixins: [ContainerMixin],
    props: {
      wrapper: {
        type: String,
        default: 'div'
      },
      helperClass: {
        type: String,
        default: 'dragging-item'
      },
      distance: {
        type: Number,
        default: 5
      }
    },
    render (h) {
      return h(this.wrapper, this.$slots.default)
    }
  }
</script>
