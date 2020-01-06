/**
 * Build JS.
 *
 * Will copy main project's package dist to module's dist folder. Files will be located in folder
 * that has main package's version name. This is useful to prevent loading cached files
 * for module.
 *
 * @since [*next-version*]
 */
const fs = require('fs')
const copyfiles = require('copyfiles')

const mainPackage = '@rebelcode/bookings-js'
const packageJson = JSON.parse(fs.readFileSync('./package.json', 'utf8'))
const mainPackageVersion = packageJson['dependencies'][mainPackage]

copyfiles([`./node_modules/${mainPackage}/dist/**`, `./dist/${mainPackageVersion}`], true, function (err) {
  if (err) console.error(err)
})
