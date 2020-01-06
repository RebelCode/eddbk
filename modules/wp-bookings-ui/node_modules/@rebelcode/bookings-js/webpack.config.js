var debug = process.env.NODE_ENV !== 'production'
var webpack = require('webpack')
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin
var ExtractTextPlugin = require('extract-text-webpack-plugin')
var VueLoaderPlugin = require('vue-loader/lib/plugin')

let config = {
  context: __dirname,
  entry: './src/app.js',
  output: {
    path: __dirname + '/dist',
    filename: 'app.min.js',
    libraryTarget: 'umd'
  },
  devtool: debug ? 'inline-sourcemap' : false,
  module: {
    rules: [
      {
        test: /\.(scss|sass)$/,
        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: [
            {loader: 'css-loader', options: {minimize: true}},
            'fast-sass-loader'
          ]
        })
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.html$/,
        loader: 'vue-template-compiler-loader'
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /(node_modules|bower_components)/,
      }
    ]
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@rebelcode/std-lib': '@rebelcode/std-lib/dist/std-lib.umd.js',
    },
    extensions: ['*', '.js', '.vue', '.json']
  },
  plugins: debug ? [
    new ExtractTextPlugin('app.min.css'),
    new VueLoaderPlugin()
  ] : [
    new ExtractTextPlugin({ // define where to save the file
      filename: 'app.min.css',
      allChunks: true
    }),
    new VueLoaderPlugin(),
    new webpack.optimize.ModuleConcatenationPlugin(),
    // new webpack.optimize.OccurrenceOrderPlugin(),
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    // new BundleAnalyzerPlugin(),
    new webpack.optimize.UglifyJsPlugin({
      sourcemap: false,
      compress: {
        warnings: false,
        pure_funcs: [
          'console.log', 'console.info', 'console.warn'
        ]
      },
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ],
}

module.exports = config
