# Datetime Picker Component
[![npm (scoped)](https://img.shields.io/npm/v/@rebelcode/vc-datetime-picker.svg)]()

## Usage
```js
<datetime-picker v-model="datetime" inline-template>
    <div>
        <span>
            {{ nativeDate }}
        </span>
        <input type="text" placeholder="YYYY-MM-DD" v-model="dateValue" readonly/>
        <input type="text" placeholder="HH:ii:ss" v-model="timeValue" readonly/>
    </div>
</datetime-picker>
```
Any implementations of date picker and time picker can be used inside `datetime-picker`. In order to use them computed `dateValue` and `timeValue` must be implemented.

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