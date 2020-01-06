const debug = process.env.NODE_ENV !== 'production'

const webpack = require('webpack')
const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")

let config = {
  mode: debug ? 'development' : 'production',
  context: __dirname,
  devtool: debug ? 'inline-sourcemap' : false,
  plugins: debug ? [
    new MiniCssExtractPlugin({
      filename: "app.min.css",
    })
  ] : [
    new webpack.optimize.OccurrenceOrderPlugin(),
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    new MiniCssExtractPlugin({
      filename: "app.min.css",
    })
  ],
  module: {
    rules: [
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /(node_modules|bower_components)/,
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              minimize: true
            }
          },
          'sass-loader'
        ],
      }
    ]
  },
  entry: './src/index.js',
  output: {
    path: __dirname + '/dist',
    filename: 'app.min.js',
    libraryTarget: 'umd',
    library: 'bookingWizard',
    umdNamedDefine: true,
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.common.js',
    }
  }
}

if (!debug) {
  config.optimization = {
    minimizer: [
      new UglifyJSPlugin({
        uglifyOptions: {
          compress: {
            warnings: false,
            drop_console: true
          }
        }
      })
    ]
  }
}

module.exports = config
