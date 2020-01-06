# Repeater Component

## Usage
```js
<repeater :collection="items">
    <div class="repeater-item" slot-scope="r">
        <span>{{ r.item.title }}</span>
        <button @click="r.remove(r.item)">x</button>
    </div>
</repeater>
```
Where `collection` has type of `FunctionalCollection` from [std-lib](https://github.com/RebelCode/std-lib).

This example will iterate through all items in `collection` object and output each item using child `repeater` code (`<div slot-scope="r"...` in our case). Next options is available in item's code:

> `{scopeName}` is string you've passed to `slot-scope` attribute inside item's template. In our example is `r`. In that template you'll be able to access to exposed methods and variables using that scope.

`{scopeName}.remove(item)` - function for removing item from the collection. Usefull when you want to add controls to your items (like `x` button for removing).

`{scopeName}.item` - item that we iterating on from our collection.

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