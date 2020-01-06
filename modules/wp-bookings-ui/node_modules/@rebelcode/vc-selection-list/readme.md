# Selection List Component
[![npm (scoped)](https://img.shields.io/npm/v/@rebelcode/vc-selection-list.svg)]()

Selection List is a Vue component that extend Repeater component from [here](https://github.com/RebelCode/vc-repeater) and used for creating components that can be used for selection item (or items) from the passed collection.

## Usage
```js
<selection-list :collection="items" v-model="selectedValue">
    <div :class="[r.isItemSelected(r.item) ? 'selected' : '']" @click="r.select(r.item)" slot-scope="r">
        <span>{{ r.item.title }}</span>
    </div>
</selection-list>
```
Where `collection` is type of `FunctionalCollection` from [here](https://github.com/RebelCode/std-lib). `v-model` var (`selectedValue` in our case) is type of `Array`. Also `selection-list` can be multiple, if `:multiple="true"` is set on `selection-list` component.

When rendering one item from the collection, all methods and objects from Repeater component and new ones available:

> `{scopeName}` is string you've passed to `slot-scope` attribute inside item's template. In our example is `r`. In that template you'll be able to access to exposed methods and variables using that scope.

`{scopeName}.isItemSelected(item)` - function for checking that item is selected.

`{scopeName}.select(item)` - function for selecting item (`@click="r.select(r.item)` in example above").

`{scopeName}.getKey(item)` - function for getting key from collection's item. This is useful when selection list wrapper is native `select`. In this case you use `getKey(item)` for provide `value` attribute for `option`. For example: `<option :value="r.getKey(r.item)>{{ r.item.title }}</option>`.

## Developing
Run `npm install` to install all dev dependencies.

Here is available npm commands.

Build library while developing
```sh
npm run dev
```

Build library for production
```sh
npm run production
```

Run e2e tests
```sh
npm run e2e
```