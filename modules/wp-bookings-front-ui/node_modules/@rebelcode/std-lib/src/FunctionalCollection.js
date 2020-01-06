/**
 * A collection of items that uses commit and get pattern.
 *
 * This implementation does not operate on a reference to
 * an item set, but instead generates new sets and commits
 * them.
 */
export class FunctionalCollection {
  /**
   * @param {Function} getItems The function used to retrieve the state of the item set.
   * @param {Function} setItems The function used to commit the new state of the item set.
   * @param {Function} keyGetter The function used to get item's key.
   */
  constructor(getItems, setItems, keyGetter) {
    if (!getItems) {
      throw new Error(`Getter must be provided`)
    }
    if (!setItems) {
      throw new Error(`Setter must be provided`)
    }
    if (!keyGetter) {
      throw new Error(`Key getter must be provided`)
    }

    this._itemsGetter = getItems
    this._itemsSetter = setItems
    this._keyGetter = keyGetter
  }

  /**
   * Commits the new state of the items set.
   *
   * @param {Object} The new items set.
   */
  _setItems(items) {
    this._itemsSetter(items)
  }

  /**
   * Retrieves a copy of the items set.
   *
   * @return {Object} The new items set. Key is item ID.
   */
  _getItems() {
    return this._itemsGetter()
  }

  /**
   * Retrieves items of the collection.
   *
   * @return {Object} The items, by item ID.
   */
  getItems() {
    return Object.assign({}, this._getItems())
  }

  /**
   * Adds items to the collection.
   *
   * @param {Object} item The item to add
   */
  addItem(item) {
    let items = this._getItems()
    items = this._addItem(items, item)
    this._setItems(items)
  }

  /**
   * Check that given item in the collection.
   *
   * @param item
   * @returns {boolean}
   */
  hasItem(item) {
    return !!this._getItems()[this._keyGetter(item)]
  }

  /**
   * Remove item from set
   *
   * @param {Object} item
   */
  removeItem(item) {
    let items = this._getItems()
    items = this._removeItem(items, item)
    this._setItems(items)
  }

  /**
   * Adds an item to a set.
   *
   * @param {Object} The item set.
   * @param {Object} The item to add.
   *
   * @return {Object} The modified items set.
   */
  _addItem(items, item) {
    items = Object.assign({}, items)
    let id = this._keyGetter(item)
    if (items[id]) {
      throw new Error(`Item with id ${id} already exists`)
    }

    items[id] = item

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
  _removeItem(items, item) {
    items = Object.assign({}, items)
    delete items[this._keyGetter(item)]
    return items
  }

  /**
   * Adds multiple items to an item set.
   *
   * @param {Object} items The item set.
   * @param {Array} newItems The items to add.
   *
   * @return {Object} The modified item set.
   */
  _addItems(items, newItems) {
    for (var i in newItems) {
      this._addItem(items, newItems[i])
    }

    return items
  }
}
