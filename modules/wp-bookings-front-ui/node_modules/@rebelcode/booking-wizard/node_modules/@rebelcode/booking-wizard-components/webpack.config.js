var debug = process.env.NODE_ENV !== 'production'
var webpack = require('webpack')
var HtmlWebpackPlugin = require('html-webpack-plugin')

let config = {
  mode: debug ? 'development' : 'production',
  context: __dirname,
  devtool: debug ? 'inline-sourcemap' : false,
  plugins: debug ? [
    new HtmlWebpackPlugin({
      template: 'index.html',
      inject: true
    })
  ] : [
    new webpack.optimize.OccurrenceOrderPlugin()
  ],
  module: {
    rules: [
      {test: /\.js$/, loader: 'babel-loader'}
    ]
  },
  entry: './src/index.js',
  output: {
    path: __dirname + '/dist',
    filename: 'lib.min.js',
    libraryTarget: 'umd',
    library: 'bookingWizardComponents',
    umdNamedDefine: true,
  }
}

module.exports = config