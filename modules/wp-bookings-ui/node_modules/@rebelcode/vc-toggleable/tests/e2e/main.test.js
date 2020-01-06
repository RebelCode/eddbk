import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that component can be successfully rendered.
 *
 * @since [*next-version*]
 */
test('Render initial state', async testController => {
    const spanSelector = await new Selector('body span');
    await testController.expect(spanSelector.innerText).eql('On');
});

/**
 * Test that component can switch toggleable state by interaction.
 *
 * @since [*next-version*]
 */
test('Can switch state by interaction', async testController => {
    const spanSelector = await new Selector('body span');
    await testController.click(spanSelector);

    setTimeout(() => {
        testController.expect(spanSelector.innerText)
            .eql('Off');
    }, 1);
});