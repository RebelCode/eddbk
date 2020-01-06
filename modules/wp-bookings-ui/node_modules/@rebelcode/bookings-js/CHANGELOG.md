# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
## [0.2.7] - 2019-01-15
### Added
- Added timezone selector to staff member's editor.

## [0.2.6] - 2018-12-05
### Changed
- Fixed broken dependencies.

## [0.2.5] - 2018-12-05
### Added
- Added a new view for sessions management.
- Added the session editor with labels.
- Added the staff members module which includes list and editor for staff members.
- Added abstract list mixins (List and Page), for managing list views of items.
- Added ability to reorder filters for session selector.

### Changed
- Using new session types.

## [0.2.4] - 2018-10-29
### Added
- Added a new page for managing services, similar to the bookings page.
- Added service editor in the modal.
- Added image selection component.

### Removed
- Removed the partial metabox application for editing and creating a service.

## [0.2.3] - 2018-10-08
### Added
- Wizard labels editor.

### Changed
- Settings pages is divided on two separate tabs, one for general settings and the other one for wizard's customizations.

## [0.2.2] - 2018-09-24
### Fixed
- Wrong datetime picker behavior when selecting dates (sometimes it highlighted wrong dates).

## [0.2.1] - 2018-09-24
### Changed
- Using version `0.1.11` of `booking-wizard-components`.

## [0.2.0] - 2018-09-18
### Changed
- All application styles now located and shipped with this package. 

## [0.1.27] - 2019-09-11
### Fixed
- Fixed wrong timezone behavior in availability calendar when client timezone is on west from Greenwich (has negative offset).

## [0.1.26] - 2018-08-27
### Changed
- For monthly repeating on week day now using "fifth" word instead of "last" to not confuse users.

### Fixed
- Correctly rendering all day availabilities using `allDay` flag of FullCalendar's event.

## [0.1.25] - 2018-08-24
### Changed
- Availability model's end time now normalized before sending to server (if `all day` is true, new day's start time will be sent).

## [0.1.24] - 2018-08-13
### Added
- UI action pipelines - configurable sets of UI actions, objects that are able to make and revert UI changes (click, add message box).
- Running UI action pipeline on "Enable Bookings" change.

## [0.1.22] - 2018-07-12
### Changed
- Services definitions for the latest version of booking wizard components (`0.1.4`).

### Added
- Added ability to change timezone in which bookings are displayed (list view, calendar view and editor) on bookings page.

## [0.1.21] - 2018-06-13
### Added
- Session length limit property for session lengths component.

## [0.1.20] - 2018-06-12
### Changed
- The DateTime picker now always produces a value in `ISO8601` format.
- `day` and `week` time units for session length are temporary disabled.

### Fixed
- Daylight saving issue during rendering of repeating all-day events in availability calendar.

## [0.1.19] - 2018-06-11
### Changed
- A logic and UX for actions in booking editor.

## [0.1.18] - 2018-06-11
### Fixed
- Minutes are shown for each item in bookings list.

## [0.1.17-alpha18] - 2018-06-09
### Added
- Rest API errors handling.

### Changed
- The default value for Repeats - Ends After is 1, not 4, for all units.
- Repeating period uses start date for determining repetition ends, not start of period.

### Fixed
- Availability calendar daylight saving problem.
