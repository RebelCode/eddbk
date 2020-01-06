import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that consumer can select item by clicking on it.
 *
 * @since [*next-version*]
 */
test('Can select items', async testController => {
    const selectItemSelector = await new Selector('#app > div > div');
    await testController.expect(selectItemSelector.innerText).eql('First item');

    await testController
        .click(selectItemSelector);

    setTimeout(() => {
        testController.expect(selectItemSelector.hasClass('selected'))
            .eql(true);
    }, 1);
});
