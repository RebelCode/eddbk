import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that we have first page rendered in the body.
 *
 * @since [*next-version*]
 */
test('First page rendered correct and contains "Hello World!"', async testController => {
    const paragraphSelector = await new Selector('body h1:first-child');
    await testController.expect(paragraphSelector.innerText).eql('Hello World!');
});

/**
 * Test that we have second page with vue component rendered in the body.
 *
 * @since [*next-version*]
 */
test('Second page with vue component rendered correct and contains "John Doe"', async testController => {
    const paragraphSelector = await new Selector('body div h2:first-child');
    await testController.expect(paragraphSelector.innerText).eql('John Doe');
});