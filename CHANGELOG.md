# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD

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
