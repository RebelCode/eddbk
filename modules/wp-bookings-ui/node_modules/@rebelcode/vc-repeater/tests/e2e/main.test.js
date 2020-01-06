import {Selector} from 'testcafe'

fixture(`Index page`)
    .page('http://localhost:8082');

/**
 * Test that component can successfully render all items in the collection.
 *
 * @since [*next-version*]
 */
test('Render all items', async testController => {
    const paragraphSelector = await new Selector('body div.repeater-item:nth-child(1) span');
    await testController.expect(paragraphSelector.innerText).eql('First item');

    const secondParagraphSelector = await new Selector('body div.repeater-item:nth-child(2) span');
    await testController.expect(secondParagraphSelector.innerText).eql('Second item');
});

/**
 * Test that component can remove item from the collection
 * using exposed function.
 *
 * @since [*next-version*]
 */
test('Can delete item from the collection using exposed slot function', async testController => {
    const removeFirstButton = await new Selector('body div.repeater-item:nth-child(1) > button');
    await testController.click(removeFirstButton);

    setTimeout(() => {
        const firstItem = Selector('body div.repeater-item:nth-child(1)');

        testController.expect(firstItem.exists)
            .eql(false);
    }, 1);
});