import {CfToggleable} from './../../src/index';

import Vue from 'vue';
import {FunctionalToggleable} from '@rebelcode/std-lib'

new Vue({
    el: '#app',
    data () {
        return {
            store: {
                switcher: true
            },

            toggleable: new FunctionalToggleable((newValue) => {
                this.store.switcher = newValue
            }, () => {
                return this.store.switcher
            })
        }
    },
    components: {
        'toggle': new CfToggleable(Vue)
    }
});
