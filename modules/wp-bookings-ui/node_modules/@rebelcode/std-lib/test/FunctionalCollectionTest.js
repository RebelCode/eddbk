import {FunctionalCollection} from './../src/FunctionalCollection';
var assert = require('assert');

/**
 * Simple items collection.
 *
 * @returns {{123: {id: number}, 125: {id: number}, 127: {id: number}}}
 */
function createNewItems() {
    return {
        123: {
            id: 123
        },
        125: {
            id: 125
        },
        127: {
            id: 127
        }
    };
}

/**
 * Create new collection based on some items.
 *
 * @param getter
 * @param setter
 * @param id
 * @returns {FunctionalCollection}
 */
function createNewCollection(getter, setter, id = null) {
    id = id ? id : (item) => {
        return item.id;
    };
    return new FunctionalCollection(getter, setter, id);
}

describe('FunctionalCollection', function() {
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

                assert.equal(items[2].id, 2);
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
                assert.equal(items[2], undefined);
            });
        });
    })
});