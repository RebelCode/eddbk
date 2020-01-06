# UI Framework

[![npm (scoped)](https://img.shields.io/npm/v/@rebelcode/ui-framework.svg)]()

Application framework, which has as its main purpose the creation of root Vue instances for specified regions of the page.

The framework is modular, each module completely contained in a folder under the same root. Each module also has as its entry point an index.js file, which returns the API of the module defined in api.js in the same folder. The API can be used to access module units from outside of the application, and represents a hierarchy of namespaces. Which modules are active, and are thus made part of the API, is determined by [`main.js`](src/main.js).

- Allows designating Vue-renderable regions of the page by selector. Supports multiple elements per selector, e.g. if a class-based selector matches multiple DOM elements.
- Allows using any components to render the designated regions, provided that the application has access to these components.
- External dependencies, including components, can be loaded asynchronously.
- All services can be replaced, including by 3rd-party code running on the same client.
- Single responsibility of components.
- Loose coupling due to dependency on abstraction.
- Modular structure.

An example of how the application can be initialized on a page is provided in [`index.html`](index.html). This example loads many of the application requirements asynchronously, including Vue itself, the `vue-fullcalendar` component, and other distributed libraries. It makes them available to the application via the DI container (wrapper provided by the [`Container module`](src/container/api.js)). This allows us to avoid shipping these components for production, as well as to re-use them on the page - such as if another plugin (or our own extension) also requires some of them.

## Modules

### Core module
The [**Core module**](src/core/api.js) contains units that define the core functionality of the application. The application requires external dependencies to be injected in a DI container.

An important feature of the [**Core module**](src/core/api.js) is that it makes all existing services available in all UI components via the `inject/provide` mechanism. This is achieved by assigning the container itself as the value for provide, making all of its properties (they contain the services) accessible via inject. Thus, components can declare dependency on specific services, and the container is responsible for fulfilling these requirements.

A special use-case for this injection mechanism is the feature which allows injecting component implementations. A component that depends on child components can inject their constructors in the usual way, and then assign them by specifying the name of their service as a string in the `components property`. This allows us to avoid global component registration (especially since Vue may be shared between apps on the same page), while still making all compoonents available for use in other components. Also, this further makes use of the fact that components are dependency-injected, adding structure to the application. Finally, this method of injecting component dependencies does not require components to be coupled to the DI container in any way. This is facilitated by a mixin returned from [`App#_componentMixin()`](src/core/App.js).

### Container module
The [**Container module**](src/container/api.js) contains units that deal with value retrieval and Dependency Injection.

### I18n module
The [**I18n module**](src/i18n/api.js) deals with internationalization. It allows usage of any internationalization mechanism that can translate strings with `sprintf()` style placeholders.

### Dom module
The [**Dom module**](src/dom/api.js) abstracts the DOM, which helps avoid dealing with the BOM, and can be very useful for running tests on the server, because the DOM can then be mocked.

## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build

# build for production and view the bundle analyzer report
npm run build --report

# run unit tests
npm run unit

# run e2e tests
npm run e2e

# run all tests
npm test
```
