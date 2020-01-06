require('./../styles/app.scss')

const get = require('lodash/get')
const has = require('lodash/has')
const set = require('lodash/set')
const defaultsDeep = require('lodash/defaultsDeep')
const debounce = require('lodash/debounce')
const differenceWith = require('lodash/differenceWith')
const isEqual = require('lodash/isEqual')
const transform = require('lodash/transform')
const isObject = require('lodash/isObject')

import axios from 'axios'
import { extendMoment } from 'moment-range'
import moment from 'moment-timezone'
import bottle from 'bottlejs'
import Vue from 'vue'
import Vuex from 'vuex'
import validate from 'vee-validate'
import datepicker from 'vuejs-datepicker'
import toasted from 'vue-toasted'
import jquery from 'jquery'
import humanizeDuration from 'humanize-duration'
import * as textFormatter from 'sprintf-js'
import * as bookingWizardComponents from '@rebelcode/booking-wizard-components'
import * as calendar from '@rebelcode/vc-calendar'
import * as fullCalendar from 'fullcalendar'
import * as repeater from '@rebelcode/vc-repeater'
import * as selectionList from '@rebelcode/vc-selection-list'
import * as tabs from '@rebelcode/vc-tabs'
import * as datetimePicker from '@rebelcode/vc-datetime-picker'
import sha1 from 'js-sha1'
import timepicker from 'vue2-timepicker/dist/vue2-timepicker.min'
import fastDeepEqual from 'fast-deep-equal'
import pluralize from 'pluralize'
import * as stdLib from '@rebelcode/std-lib'
import * as uiFramework from '@rebelcode/ui-framework'
import * as cfToggleable from '@rebelcode/vc-toggleable'
import vueselect from 'vue-select'
import wpListTable from 'vue-wp-list-table/dist/vue-wp-list-table.common'
import { Sketch } from 'vue-color'


const dependencies = {
  cfToggleable,
  uiFramework,
  bottle,
  vue: Vue,
  vuex: Vuex,
  momentRange: { extendMoment },
  validate,
  toasted,
  jquery,
  lodash: {has, get, set, defaultsDeep, debounce, differenceWith, isEqual, transform, isObject},
  textFormatter,
  stdLib,
  bookingWizardComponents,
  axios,
  calendar,
  pluralize,
  vueselect,
  datetimePicker,
  tabs,
  sha1,
  datepicker,
  timepicker,
  repeater,
  selectionList,
  fullCalendar,
  moment,
  fastDeepEqual,
  wpListTable,
  humanizeDuration,

  vueColor: {Sketch}
}

import { services } from './services'

/*
 * This is entry point for application.
 *
 * Require JS will be used here to load all needed
 * dependencies (Vue at least).
 */
document.addEventListener('DOMContentLoaded', function () {
  const di = new dependencies.bottle()
  defineServices(di, dependencies)

  const container = new uiFramework.Container.Container(di)
  const app = new uiFramework.Core.App(container)
  app.init()

  function defineServices (di, dependencies) {
    const applicationState = window.EDDBK_APP_STATE

    let serviceList = services(dependencies, applicationState, document)

    serviceList['APP_STATE'] = function () {
      return applicationState
    }

    serviceList['selectorList'] = function () {
      return [
        '#eddbk-bookings-page',
        '#eddbk-service-page',
        '#eddbk-services-page',
        '#eddbk-staff-members-page',
        '#eddbk-bookings-screen-options',
        '#eddbk-settings-page'
      ]
    }

    for (var i = 0; i < Object.keys(serviceList).length; i++) {
      const serviceName = Object.keys(serviceList)[i]
      di.factory(serviceName, serviceList[serviceName])
    }
  }
})
