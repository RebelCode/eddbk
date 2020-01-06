export default {
  data () {
    return {
      defaultStaffImageSrc: 'http://1.gravatar.com/avatar/409c4050b1f39b6d0049c56f9fbb79e5?s=192&d=mm&r=g',
    }
  },
  methods: {

    /**
     * Whether given session types have resource with given type.
     *
     * @since [*next-version*]
     *
     * @param {string} resourceType
     * @param {ServiceSessionType[]} sessionTypes
     *
     * @return {boolean}
     */
    hasResources (resourceType, sessionTypes) {
      console.info({sessionTypes})
      for (let sessionType of sessionTypes) {
        for (let resource of (sessionType.data.resources || [])) {
          if (resource && resource.type === resourceType) {
            return true
          }
        }
      }
      return false
    },

    /**
     * Get resources of types have resource with given type.
     *
     * @since [*next-version*]
     *
     * @param {string} resourceType
     * @param {object[]} resourceHolders
     *
     * @return {boolean}
     */
    getResources (resourceType, resourceHolders) {
      let resources = []
      for (let resourceHolder of resourceHolders) {
        const allResources = resourceHolder.data ? resourceHolder.data.resources : resourceHolder.resources
        for (let resource of allResources) {
          if (resource && resource.type === resourceType && !resources.find(r => r.id === resource.id)) {
            resources.push(resource)
          }
        }
        console.info(resources)

      }
      return resources
    },
  }
}
