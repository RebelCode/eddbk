import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that vue wrapper renders full calendar as expected.
 *
 * @since [*next-version*]
 */
test('Render calendar without errors', async testController => {
    const calendarLabelSelector = await new Selector('.fc .fc-body .fc-axis span');
    await testController.expect(calendarLabelSelector.innerText).eql('all-day');
});

/**
 * Test that vue wrapper renders full calendar's events
 * that passed when component using component. Also check that
 * buttons work well in rendered component.
 *
 * @since [*next-version*]
 */
test('Render events in calendar', async testController => {
    const monthButtonSelector = await new Selector('.fc .fc-month-button');

    await testController
        .click(monthButtonSelector);

    setTimeout(() => {
        const firstEventSelector = new Selector('.fc-event-container .fc-content .fc-title');

        testController.expect(firstEventSelector.innerText)
            .eql('First Event');
    }, 1);
});