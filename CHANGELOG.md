# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Fixed
- The confirmation page showed `{service_name}` instead of the actual service names.

## [0.2] - 2018-10-30
### Added
- An icon button on admin pages for direct inline access to documentation and support.
- A new admin page that lists services and provides creation and editing capabilities.
- Services may now have an assigned color, for admin display purposes only.
- New protected REST API service routes for creation, updating and deletion of services.

### Changed
- The availability builder interface for services has been redesigned.
- Availability rules are now displayed in a list in the availability builder.
- Services are now hidden from the EDD Downloads list table.
- Sessions are now generated asynchronously using WP Cron when a service has been saved.

### Removed
- The bookings metabox in the Download new/edit page has been removed in favor of the new services page.

## [0.1.5] - 2018-10-08
### Added
- The text and labels for the booking wizard can now be customized from the settings page.
- A `services.php` file for core, non-initial services.
- A `config.php` file for core configuration. 
- The main plugin class includes plugin information and config in the main container.

### Fixed
- The plugin would show an update for the latest version, even when the latest version was installed.
- Saving a download as draft did not retrieve its booking options from the database.

### Changed
- The main plugin class now accepts plugin info instead of a module key as its first constructor parameter.

## [0.1.4] - 2018-09-24
### Fixed
- Services with a draft, private, protected or future status were being shown in service selection lists.
- The calendar in the booking wizard did not render correctly on some mobile devices.
- The Download new/edit page would break when the WordPress site uses other plugins that also use RequireJs.
- Date pickers in the availability modal popup would not disable past dates relative to the first period's start date.
- Changing the timezone in the booking wizard caused the already select day to remain selected even when there were no available sessions.
- Selecting a date for the date picker's first available period caused the used date to be 1 day off.

## [0.1.3] - 2018-09-13
### Fixed
- The front-end wizard ignoring the "Week Starts On" setting.
- Selecting a day in the front-end wizard calendar wrongly highlights the previous day for certain negative timezones.

## [0.1.2] - 2018-09-12
### Changed
- Using version `0.1-alpha8` of `rcmod-eddbk-rest-api`.
- Using version `0.1-alpha14` of `rcmod-wp-bookings-front-ui`.
- Using version `0.1-alpha19` of `rcmod-wp-bookings-ui`.

## [0.1.1] - 2018-08-31
### Changed
- Using version `0.1-alpha16` of `rcmod-wp-bookings-ui`.
- Using version `0.1-alpha13` of `rcmod-wp-bookings-front-ui`.

## [0.1] - 2018-08-29
Stable release.

## [0.1-rc2] - 2018-08-28
### Changed
- Using newer versions of some modules.
- UI improvements.

## [0.1-rc1] - 2018-08-13
### Changed
- Using newer versions of most modules.

## [0.1-beta5] - 2018-07-27
### Changed
- Using newer versions of some modules.

### Added
- Settings page.
- Licensing module, which allows entering the add-on license.

## [0.1-beta4] - 2018-07-13
### Changed
- Using newer version of Front UI module, which fixes issue with missing styling.

## [0.1-beta3] - 2018-07-13
### Changed
- Using new version of Shortcode module.

## [0.1-beta2] - 2018-07-12
### Changed
- Using newer versions of some modules - check their respective changelogs!
- Package name is now `eddbk`, which correspond to the repo name.
- Added checks for some environment factors: WP and EDD versions.

## [0.1-beta1] - 2018-06-13
Initial beta release, based on `0.1-alpha13`.

## [0.1-alpha13] - 2018-06-13
### Changed
- Using newer version of `session-generator`, which fixes a bug related to duplicate session generation when using same start times with multiple lengths.

## [0.1-alpha12] - 2018-06-13
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha11] - 2018-06-12
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha10] - 2018-06-11
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha9] - 2018-06-11
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha8] - 2018-06-07
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha7] - 2018-06-06
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha6] - 2018-06-05
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha5] - 2018-06-04
### Changed
- Using newer versions of some modules - check their respective changelogs!

## [0.1-alpha4] - 2018-05-24
### Changed
- Using newer versions of some modules.

## [0.1-alpha3] - 2018-05-21
### Changed
- Now requiring modules at specific tags, instead of branches.

### Fixed
- Multiple bugs; see changed modules.

## [0.1-alpha2] - 2018-05-18
### Changed
- Improved build process; can now build release archive.
- Increased VM memory to 2GB due to Composer OOM errors.
- Now using Composer cleanup plugin to clean dependencies and reduce build size.
- The build version is not determined by `version` property.
- Build runs composer in "no interaction" mode.
- Now using newer version of `modular` package.

## [0.1-alpha1] - 2018-05-17
Initial version.
