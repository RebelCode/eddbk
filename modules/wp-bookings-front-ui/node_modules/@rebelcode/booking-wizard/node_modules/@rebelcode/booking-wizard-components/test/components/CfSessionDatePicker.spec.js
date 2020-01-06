import { shallowMount } from '@vue/test-utils'

import CfDateNavigator from './../../src/components/CfSessionDatePicker'
import MfCreateDatetimeCapable from './../../src/mixins/MfCreateDatetimeCapable'

/**
 * Use Cases:
 *
 * Note: this tests should be run with timezone sliding window to be sure that it works
 * correctly in different timezones.
 *
 * - Datepicker highlight correct days (days with sessions of chosen length).
 * - Date with sessions get highlighted after clicking on that date.
 * - Unselects (emit events) session and date when duration is changed (why this component is responsible for this?)
 * - 
 *
 * ??? Maybe this should be tested in the root components.
 * - When visiting unseen month picker tries to load sessions from server.
 * - When visiting seen month server is not touched.
 * ???
 *
 *
 */

/**
 * Make and mount date navigator component.
 *
 * @since [*next-version*]
 *
 * @param propsData
 *
 * @return {Wrapper<Vue>}
 */
const mountDateNavigator = (propsData = {}) => {
  const createDatetimeCapable = MfCreateDatetimeCapable({})
  const component = CfDateNavigator(createDatetimeCapable, {})
  return shallowMount(Object.assign({}, component, {template: '<div></div>'}), {
    propsData
  })
}

/**
 * Values of DateNavigator props.
 *
 * @since [*next-version*]
 *
 * @type {{selectedDay: string, prevAvailableDay: string, nextAvailableDay: string}}
 */
const dateNavigatorProps = {
  selectedDay: 'current-day',
  prevAvailableDay: 'previous-formatted-date',
  nextAvailableDay: 'next-formatted-date'
}

describe('CfDateNavigator.js', () => {
  /**
   * Test that date navigator can successfully switch to the next day.
   */
  it('switches forward', () => {
    const wrapper = mountDateNavigator(dateNavigatorProps)

    wrapper.vm.goToNextDay()

    expect(wrapper.emitted()['update:selectedDay']).toBeTruthy()
    expect(wrapper.emitted()['update:selectedDay'][0]).toEqual([dateNavigatorProps.nextAvailableDay])
  })

  /**
   * Test that date navigator can successfully switch to the previous day.
   */
  it('switches backward', () => {
    const wrapper = mountDateNavigator(dateNavigatorProps)

    wrapper.vm.goToPrevDay()

    expect(wrapper.emitted()['update:selectedDay']).toBeTruthy()
    expect(wrapper.emitted()['update:selectedDay'][0]).toEqual([dateNavigatorProps.prevAvailableDay])
  })
})