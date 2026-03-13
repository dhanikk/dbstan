# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
### Added
- Copy buttons for commonly used README commands.
- Detailed comments for all `config/dbstan.php` configuration variables.

### Fixed
- Added preflight checks for missing database configuration/connection.
- Added migration readiness checks to guide users to run `php artisan migrate` first.
- Improved user-facing messages for setup issues in both CLI and web analysis output.

## [1.0.1] - 2026-02-13
### Added
- Initial release of orphan risk check

## [1.0.2] - 2026-02-13
### Fixed
- Minor bug fixes

## [1.0.3] - 2026-02-26
### Added
- URL-based logic implemented
- Configuration files added
- Commands can be run based on mode

### Changed
- Refactored logic to select between command or URL mode

### Fixed
- No critical fixes in this release

## [1.0.4] - 2026-03-09
### Fixed
- Minor bug fixes
- Updated README.md

## [1.0.5] - 2026-03-09
### Fixed
- Added logic to show a message when the database is not selected.
- Updated error color naming by type.
- Added a loader while scanning the database.