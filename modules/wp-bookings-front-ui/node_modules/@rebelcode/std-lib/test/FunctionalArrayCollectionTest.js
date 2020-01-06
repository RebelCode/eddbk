import {FunctionalArrayCollection} from './../src/FunctionalArrayCollection.js';
var assert = require('assert');

/**
 * Simple items collection.
 *
 * @return {*[]}
 */
function createNewItems() {
    return [{
        id: 123
    }, {
        id: 125
    }, {
        id: 127
    }];
}

/**
 * Find item by id property
 *
 * @param items
 * @param id
 * @return {*}
 */
function findById(items, id) {
    for(let item of items) {
        if(item.id === id) {
            return item;
        }
    }

    return undefined;
}

/**
 * Create new collection based on some items.
 *
 * @param getter
 * @param setter
 * @param id
 * @returns {FunctionalArrayCollection}
 */
function createNewCollection(getter, setter, id = null) {
    id = id ? id : (item) => {
        return item.id;
    };
    return new FunctionalArrayCollection(getter, setter, id);
}

describe('FunctionalArrayCollection', function() {
    describe('functional', function () {
        it('works like expected', function () {
            let items = createNewItems();
            let sampleCollection = createNewCollection(() => {return items;}, (newItems) => {items = newItems;});

            /**
             * Check collection's items getter.
             */
            assert.deepEqual(sampleCollection.getItems(), items);

            /**
             * Test collection has item checker.
             */
            assert.equal(sampleCollection.hasItem({
                id: 127
            }), true);

            /**
             * Test collection's item adding.
             */
            let testItem = {
                id: 1
            };
            sampleCollection.addItem(testItem);
            assert.equal(sampleCollection.hasItem(testItem), true);

            /**
             * Test collection's ability to remove items.
             */
            sampleCollection.removeItem(testItem);
            assert.equal(sampleCollection.hasItem(testItem), false);
        })
    });
    describe('unit', function () {
        /**
         * Test proper creation of collection.
         */
        describe('#constructor', function () {
            it('should fail when not all requirements passed', function () {
                assert.throws(function() {
                    createNewCollection();
                });
            });

            it('should not fail if parameters are passed right', function () {
                assert.doesNotThrow(function() {
                    let items = createNewItems();
                    createNewCollection(() => {return items;}, (newItems) => {items = newItems;});
                });
            });
        });

        /**
         * Test collection's item adding.
         */
        describe('#addItem(item)', function() {
            it('add new item to the real store', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(() => {return items;}, (newItems) => {items = newItems;});

                sampleCollection.addItem({
                    id: 2
                });

                let foundItem = findById(items, 2);

                assert.equal(foundItem.id, 2);
            });
        });

        /**
         * Test collection's ability to remove items.
         */
        describe('#removeItem(item)', function() {
            it('remove item from the real store', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(() => {return items;}, (newItems) => {items = newItems;});
                sampleCollection.removeItem({
                    id: 2
                });

                let foundItem = findById(items, 2);

                assert.equal(foundItem, undefined);
            });
        });

        /**
         * Test collection's items
         */
        describe('#getItems()', function() {
            it('must return array', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(() => {return items;}, (newItems) => {items = newItems;});

                assert.equal(Array.isArray(sampleCollection.getItems()), true);
            });
        });

        /**
         * Test collection's method hasItem
         */
        describe('#hasItem(item)', function() {
            it('check item in collection', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(() => {return items;}, (newItems) => {items = newItems;});

                assert.equal(sampleCollection.hasItem({
                    id: 127
                }), findById(items, 127).id !== undefined);
            });
        });
    })
});