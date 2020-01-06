import {CfDatetimePicker} from './../../src/index';
import Vue from 'vue';

new Vue({
    el: '#app',
    data () {
        return {
            datetime: new Date()
        }
    },
    components: {
        'datetime-picker': new CfDatetimePicker(Vue)
    }
});
