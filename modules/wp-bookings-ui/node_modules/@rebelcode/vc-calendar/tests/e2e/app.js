import Vue from 'vue';
import $ from 'jquery';
import moment from 'moment';
import _ from 'lodash';
import fullCalendar from 'fullcalendar';

import {CfFullCalendar} from './../../src/index';

new Vue({
    el: '#app',
    data () {
        return {
            events: [
                {
                    id: 1,
                    title: 'First event',
                    start: moment().format('YYYY-MM-DD')
                },
                {
                    id: 2,
                    title: 'Second event',
                    start: moment().format('YYYY-MM-DD')
                }
            ]
        }
    },
    components: {
        'full-calendar': new CfFullCalendar(Vue, $, _.defaultsDeep)
    }
});
