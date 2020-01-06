import template from './template.html'

/**
 * The staff members list component.
 *
 * @since [*next-version*]
 *
 * @param mapStore
 *
 * @return {*}
 */
export default function (makeItemsListMixin, mapStore) {
  return {
    ...template,
    data () {
      return {
        /**
         * @var {string} defaultImageUrl Default URL for the staff member.
         *
         * @since [*next-version*]
         */
        defaultImageSrc: 'http://1.gravatar.com/avatar/409c4050b1f39b6d0049c56f9fbb79e5?s=192&d=mm&r=g'
      }
    },
    mixins: [
      makeItemsListMixin(mapStore, 'staffMembers')
    ]
  }
}
