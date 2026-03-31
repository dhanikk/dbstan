# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
### Added
- Copy buttons for commonly used README commands.
- Detailed comments for all `config/dbstan.php` configuration variables.
- Storage engine consistency check (non-InnoDB and mixed-engine detection).
- Charset and collation consistency check (utf8 vs utf8mb4 mismatch and mixed collations).
- Auto-increment risk check for near-limit and high-growth overflow scenarios.
- Index cardinality analysis to detect low-selectivity potentially useless indexes.
- Composite index recommendation check for common `user_id + status/state` filters.
- Database-level size monitoring with threshold-based alerting.
- Table storage dominance detection and unusually large table warnings.
- Primary key presence check for all tables.

### Fixed
- Added preflight checks for missing database configuration/connection.
- Added migration readiness checks to guide users to run `php artisan migrate` first.
- Improved user-facing messages for setup issues in both CLI and web analysis output.

### Changed
- Enhanced table size analysis to include data/index split and ratio versus full database size.
- Expanded schema metadata extraction to include engine, collation, row estimate, and auto-increment details for advanced checks.
- Enhanced data type appropriateness with column-name based heuristics (`price/amount/total`, `is_/has_`, `*_at`, `email`).
- Enhanced index cardinality analysis with status/flag standalone-index misuse warnings.
- Updated pivot table structure rule to allow timestamps (aligning with global timestamp policy).

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