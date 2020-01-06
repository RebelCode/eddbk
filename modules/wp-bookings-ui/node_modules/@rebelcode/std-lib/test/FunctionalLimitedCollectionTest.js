import {FunctionalLimitedCollection, FLC_OVERFLOW_PREPEND, FLC_OVERFLOW_THROW} from './../src/FunctionalLimitedCollection.js';
var assert = require('assert');

/**
 * Simple items collection.
 *
 * @since [*next-version*]
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
 * Create new collection based on some items.
 *
 * @since [*next-version*]
 *
 * @param getter
 * @param setter
 * @param id
 * @param limit
 * @param mode
 * @returns {FunctionalLimitedCollection}
 */
function createNewCollection(getter, setter, id = null, limit = 3, mode = FLC_OVERFLOW_PREPEND) {
    id = id ? id : (item) => {
        return item.id;
    };
    return new FunctionalLimitedCollection(getter, setter, id, limit, mode);
}

/**
 * @since [*next-version*]
 */
describe('FunctionalLimitedCollection', function() {
    /**
     * @since [*next-version*]
     */
    describe('functional', function () {
        /**
         * @since [*next-version*]
         */
        it('works like expected', function () {
            let items = createNewItems();
            let sampleCollection = createNewCollection(
                () => {return items;},
                (newItems) => {items = newItems;},
                (item) => {return item.id;},
                3,
                FLC_OVERFLOW_THROW
            );

            /**
             * Check collection's items getter.
             */
            assert.deepEqual(sampleCollection.getItems(), items);

            /**
             * Test collection throw error if we have too much items.
             */
            assert.throws(function() {
                sampleCollection.addItem({
                    id: 1
                });
            });

            /**
             * Collection that add item on start
             *
             * @type {FunctionalLimitedCollection}
             */
            sampleCollection = createNewCollection(
                () => {return items;},
                (newItems) => {items = newItems;},
                (item) => {return item.id;},
                3,
                FLC_OVERFLOW_PREPEND
            );

            /**
             * Add item to collection. It should add it and not fail.
             */
            sampleCollection.addItem({
                id: 1
            });
            assert.equal(sampleCollection.hasItem({
                id: 1
            }), true);
        })
    });

    /**
     * @since [*next-version*]
     */
    describe('unit', function () {
        /**
         * Test proper creation of collection.
         *
         * @since [*next-version*]
         */
        describe('#constructor', function () {
            /**
             * @since [*next-version*]
             */
            it('should fail when not all requirements passed', function () {
                assert.throws(function() {
                    createNewCollection();
                });
            });

            /**
             * @since [*next-version*]
             */
            it('should not fail if parameters are passed right', function () {
                assert.doesNotThrow(function() {
                    let items = createNewItems();
                    createNewCollection(
                        () => {return items;},
                        (newItems) => {items = newItems;},
                        (item) => {return item.id;},
                        3,
                        FLC_OVERFLOW_PREPEND
                    );
                });
            });

            /**
             * @since [*next-version*]
             */
            it('should fail when too many items passed and mode is throw', function () {
                assert.throws(function() {
                    let items = createNewItems();
                    createNewCollection(
                        () => {return items;},
                        (newItems) => {items = newItems;},
                        (item) => {return item.id;},
                        2,
                        FLC_OVERFLOW_THROW
                    );
                });
            });
        });

        /**
         * Test collection item adding in different modes.
         *
         * @since [*next-version*]
         */
        describe('#addItem(item)', function() {
            /**
             * @since [*next-version*]
             */
            it('throw if adding items in throw mode', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(
                    () => {return items;},
                    (newItems) => {items = newItems;},
                    (item) => {return item.id;},
                    3,
                    FLC_OVERFLOW_THROW
                );

                assert.throws(function() {
                    sampleCollection.addItem({
                        id: 1
                    });
                });
            });

            /**
             * @since [*next-version*]
             */
            it('add new item on the first place in start mode', function() {
                let items = createNewItems();
                let sampleCollection = createNewCollection(
                    () => {return items;},
                    (newItems) => {items = newItems;},
                    (item) => {return item.id;},
                    3,
                    FLC_OVERFLOW_PREPEND
                );

                sampleCollection.addItem({
                    id: 1
                });
                assert.equal(items[0].id, 1);
            });
        });
    })
});