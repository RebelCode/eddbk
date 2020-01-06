<template>
    <div class="v-image-selector">
        <div class="v-image-selector__holder" @click="openGallery">
            <div v-if="!value.id">
                <span class="dashicons dashicons-camera"></span>
            </div>
            <div class="v-image-selector__preview" :style="previewStyle" v-else></div>
        </div>
        <div class="v-image-selector__clear" @click="removeImage" v-if="value.id">{{ _('Remove image') }}</div>
    </div>
</template>

<script>
  export default {
    inject: {
      /**
       * Translating function.
       *
       * @since [*next-version*]
       */
      '_': {
        from: 'translate'
      }
    },
    props: {
      /**
       * Image selector model.
       *
       * @since [*next-version*]
       *
       * @var {{id: number,url: string}} value
       */
      value: {
        default () {
          return {
            id: null,
            url: null
          }
        }
      },
    },
    computed: {
      /**
       * Style for the preview block of the selected image.
       *
       * @since [*next-version*]
       *
       * @return {{'background-image': string}}
       */
      previewStyle () {
        return {
          'background-image': `url(${this.value.url})`
        }
      }
    },
    methods: {
      /**
       * Remove selected image
       *
       * @since [*next-version*]
       */
      removeImage () {
        this.$emit('input', {
          id: null,
          url: null
        })
      },

      /**
       * Open gallery for image selection.
       *
       * @since [*next-version*]
       */
      openGallery () {
        var imageFrame

        if (imageFrame) {
          imageFrame.open()
          return
        }

        // Define imageFrame as wp.media object
        imageFrame = wp.media({
          title: 'Select Media',
          multiple: false,
          library: {
            type: 'image',
          }
        })

        imageFrame.on('close', () => {
          const selection = imageFrame.state().get('selection')

          let selectedAttachment = null
          selection.each((attachment) => {
            console.info({attachment})
            selectedAttachment = attachment
          })

          if (!selectedAttachment || !selectedAttachment.id) {
            return
          }

          this.$emit('input', {
            id: selectedAttachment.id,
            url: selectedAttachment.attributes.url
          })
        })

        imageFrame.on('open', () => {
          const selection = imageFrame.state().get('selection')
          if (this.value.id) {
            const attachment = wp.media.attachment(this.value.id)
            attachment.fetch()
            selection.add(attachment ? [attachment] : [])
          }
        })

        imageFrame.open()
      }
    }
  }
</script>