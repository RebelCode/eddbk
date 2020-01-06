import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that component can be successfully rendered.
 *
 * @since [*next-version*]
 */
test('Render component without errors', async testController => {
    const paragraphSelector = await new Selector('body div span.native-date');
    await testController.expect(paragraphSelector.exists).eql(true);
});

/**
 * Test that component can render date computed property.
 *
 * @since [*next-version*]
 */
test('Date getter work', async testController => {
    const dateInput = await new Selector('body .date-input');
    const date = new Date();
    const dateString = date.toISOString().substring(0, 10);
    await testController.expect(dateInput.value).eql(dateString);
});