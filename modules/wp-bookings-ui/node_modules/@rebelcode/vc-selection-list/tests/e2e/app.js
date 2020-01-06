import {CfSelectionList} from './../../src/index';

import {FunctionalCollection} from '@rebelcode/std-lib'
import {CfRepeater} from '@rebelcode/vc-repeater';

import Vue from 'vue';

new Vue({
    el: '#app',
    data () {
        return {
            selectedValue: [],
            store: {
                items: {
                    1: {
                        id: 1,
                        title: 'First item'
                    },
                    2: {
                        id: 2,
                        title: 'Second item'
                    }
                }
            },

            items: new FunctionalCollection(() => {
                return this.store.items
            }, (newItems) => {
                this.store.items = newItems
            }, (item) => {
                return item.id
            })
        }
    },
    components: {
        'selection-list': new CfSelectionList(new CfRepeater(Vue))
    }
});
