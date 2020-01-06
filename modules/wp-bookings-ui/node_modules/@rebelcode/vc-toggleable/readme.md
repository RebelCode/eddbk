# Toggleable Component
[![npm (scoped)](https://img.shields.io/npm/v/@rebelcode/vc-toggleable.svg)]()

## Usage
```js
<toggle :toggleable="toggleable" inline-template>
    <span @click="setState(!isOn)">
        {{ isOn ? 'On' : 'Off' }}
    </span>
</toggle>
```
Toggleable component implements logic responsible for getting Toggle model state and setting it. So consumer needs to use `inline-template`. Inside that template next functions and variables available: 

`setState({boolean} newState)` - set toggle model state

`isOn` - is toggle model state set to `true`

Also, `toggleable` property receives object, instance of [FunctionalToggleable](https://github.com/RebelCode/std-lib/blob/task/functional-toggleable/src/FunctionalToggleable.js)

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
