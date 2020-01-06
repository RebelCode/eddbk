import {CfRepeater} from './../../src/index';

import Vue from 'vue';
import {FunctionalCollection} from './../../node_modules/std-lib/src/FunctionalCollection'

new Vue({
    el: '#app',
    data () {
        return {
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
        'repeater': new CfRepeater(Vue)
    }
});
