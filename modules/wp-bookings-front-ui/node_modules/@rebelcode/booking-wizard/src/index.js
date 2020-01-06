import './../style/app.scss'

import { Bookings } from './Bookings'
import { Services } from './Services/index'
import { Core } from './Core'

import { default as makeServices } from './services'

const bookingWizard = {
  Bookings,
  Services,
  Core,
  makeServices
}

import axios from 'axios'
import { extendMoment } from 'moment-range'
import moment from 'moment-timezone'
import bottle from 'bottlejs'
import Vue from 'vue'
import Vuex from 'vuex'
import datepicker from 'vuejs-datepicker'
import humanizeDuration from 'humanize-duration'
import * as textFormatter from 'sprintf-js'
import * as bookingWizardComponents from '@rebelcode/booking-wizard-components'
import * as stdLib from '@rebelcode/std-lib'
import * as uiFramework from '@rebelcode/ui-framework'
import lodash from 'lodash'
import * as formWizard from 'vue-form-wizard'

const dependencies = {
  bookingWizard,
  uiFramework,
  bottle,
  vue: Vue,
  vuex: Vuex,
  momentRange: { extendMoment },
  lodash,
  humanizeDuration,
  textFormatter,
  stdLib,
  bookingWizardComponents,
  axios,
  datepicker,
  moment,
  humanizeDuration,
  formWizard
}

document.addEventListener('DOMContentLoaded', function () {
  const di = new dependencies.bottle()
  defineServices(di, dependencies)

  const container = new uiFramework.Container.Container(di)
  const app = new uiFramework.Core.App(container)

  container.container.vue.mixin(container.container.TranslateCapable)

  app.init()

  function defineServices (di, dependencies) {
    var serviceList = dependencies.bookingWizard.makeServices(dependencies)

    serviceList['state'] = function () {
      /**
       * @typedef {Object} AppState
       *
       * @since [*next-version*]
       *
       * @property {string} applicationSelector Application selector for UI framework (UI framework will mount application to each of them).
       * @property {{bookings: string, services: string}} apiEndpointUrls Map of endpoint name to its API url.
       * @property {string} initialBookingTransition Name of booking transition for creating booking.
       * @property {object} datetimeFormats Map of datetime keys to datetime formats for formatting datetimes in application.
       */

      /**
       * @var {AppState} EDDBK_WIZARD_APP_STATE
       */
      return window.EDDBK_WIZARD_APP_STATE
    }
    serviceList['selectorList'] = function (container) {
      return [
        container.state.applicationSelector
      ]
    }
    serviceList['config'] = function (container) {
      return {
        endpoints: {
          sessions: {
            'fetch': {
              'method': 'get',
              'endpoint': container.state.apiEndpointUrls.sessions,
            }
          }
        },
        bookingsResourceUrl: container.state.apiEndpointUrls.bookings,
        servicesResourceUrl: container.state.apiEndpointUrls.services,
        bookingDataMap: container.state.bookingDataMap,
        initialBookingTransition: container.state.initialBookingTransition,
        datetime: container.state.datetimeFormats
      }
    }
    serviceList['document'] = function () {
      return document
    }
    serviceList['handleBookSuccess'] = function () {
      /**
       * Function for handling booking success.
       * `this` - is application Vue component inside function scope.
       *
       * @since [*next-version*]
       */
      return function () {
        if (this.config.redirectUrl) {
          window.location.href = this.config.redirectUrl
        }
      }
    }

    for (var i = 0; i < Object.keys(serviceList).length; i++) {
      var serviceName = Object.keys(serviceList)[i]
      di.factory(serviceName, serviceList[serviceName])
    }
  }

  function loadCss(url) {
    var link = document.createElement("link");
    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = url;
    document.getElementsByTagName("head")[0].appendChild(link);
  }

  const asyncStyles = window.EDDBK_WIZARD_REQUIRE_STYLES

  if (asyncStyles) {
    for (let styleLink of asyncStyles) {
      loadCss(styleLink)
    }
  }
})
