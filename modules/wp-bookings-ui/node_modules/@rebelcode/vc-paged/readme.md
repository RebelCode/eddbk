# Paged Component

## Usage
```js
<paged :items="items" :selected-keys="selectedItems"></paged>
```

Where `items` has type of `FunctionalCollection` and `selectedItems` has type of `FunctionalLimitedCollection` from [std-lib](https://github.com/RebelCode/std-lib).

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