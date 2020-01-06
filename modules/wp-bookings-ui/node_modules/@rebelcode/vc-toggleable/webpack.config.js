var debug = process.env.NODE_ENV !== "production";
var webpack = require('webpack');

let config = {
    context: __dirname,
    devtool: debug ? "inline-sourcemap" : false,
    plugins: debug ? [] : [
        new webpack.optimize.DedupePlugin(),
        new webpack.optimize.OccurrenceOrderPlugin(),
        new webpack.optimize.UglifyJsPlugin({ mangle: false, sourcemap: false }),
    ],
    module: {
        rules: [
            { test: /\.js$/, loader: "babel-loader" }
        ]
    }
};

let entries = {
    'e2e': {
        entry: "./tests/e2e/app.js",
        output: {
            path: __dirname + "/build/js",
            filename: "app.min.js",
            libraryTarget: 'umd'
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.js'
            }
        }
    },
    'default': {
        entry: "./src/index.js",
        output: {
            path: __dirname + "/dist/umd",
            filename: "lib.min.js",
            libraryTarget: 'umd'
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.js'
            }
        }
    }
}

let e2e = process.env.NODE_ENV === 'e2e';
config = Object.assign(config, entries[e2e ? 'e2e' : 'default']);

module.exports = config;