import { shallowMount } from '@vue/test-utils'

import CfDateNavigator from './../../src/components/CfDateNavigator'
import MfCreateDatetimeCapable from './../../src/mixins/MfCreateDatetimeCapable'

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