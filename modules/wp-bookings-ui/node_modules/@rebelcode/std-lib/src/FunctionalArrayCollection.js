import {FunctionalCollection} from './FunctionalCollection'

/**
 * Functional collection that works with arrays under the hood
 */
export class FunctionalArrayCollection extends FunctionalCollection {
  /**
   * Retrieves items of the collection.
   *
   * @return {Array} The items, by item ID.
   */
  getItems () {
    return this._getItems().slice()
  }

  /**
   * Check that given item in the collection.
   *
   * @param item
   * @returns {boolean}
   */
  hasItem (item) {
    let foundItemIndex = null
    let items = this._getItems()
    let itemKey = this._keyGetter(item)

    for (let i in items) {
      if (this._keyGetter(items[i]) === itemKey) {
        foundItemIndex = i
        break
      }
    }

    return foundItemIndex !== null
  }

  /**
   * Adds an item to a set.
   *
   * @param {Array} items The item set.
   * @param {Object} item The item to add.
   *
   * @return {Object} The modified items set.
   */
  _addItem (items, item) {
    items = items.slice()
    items.push(item)
    return items
  }

  /**
   * Remove item from set
   *
   * @param items
   * @param item
   * @returns {*}
   * @private
   */
  _removeItem (items, item) {
    let foundItemIndex = null
    let itemKey = this._keyGetter(item)
    for (let i in items) {
      if (this._keyGetter(items[i]) == itemKey) {
        foundItemIndex = i
        break
      }
    }
    if (foundItemIndex !== null) {
      items.splice(foundItemIndex, 1)
    }
    return items
  }
}
