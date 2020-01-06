// rollup.config.js
import resolve from 'rollup-plugin-node-resolve'
import babel from 'rollup-plugin-babel'
import { uglify } from 'rollup-plugin-uglify'

const format = process.env.FORMAT || 'umd'
const production = process.env.PRODUCTION || false
const isEsmModuleFormat = format === 'esm'

export default {
  input: 'src/index.js',
  output: {
    file: 'dist/std-lib.' + format + '.js',
    format: format,
    name: 'stdLib'
  },
  plugins: [
    production && !isEsmModuleFormat ? uglify() : {},
    resolve(),
    babel({
      'presets': isEsmModuleFormat ? [] : [
        ['env', {'modules': false}],
        'es2015-rollup'
      ],
      'plugins': ['transform-class-properties']
    })
  ]
}
