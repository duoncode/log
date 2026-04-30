# Changelog

## [Unreleased](https://github.com/duoncode/log/compare/0.1.0...HEAD)

### Changed

- Use PSR-3 string log levels for logger constants and `level` filtering.
- Rename the logger constructor destination from `logfile` to `file` and reorder `level` before `formatter`.
- Replace the old message, template, and context formatters with the default `TextFormatter` and explicit `PlainFormatter`.
- Use timezone-aware `DATE_ATOM` timestamps in log records.
- Normalize default SAPI log records to one physical line while preserving multiline explicit file logs.
- Throw `Psr\Log\InvalidArgumentException` for invalid log levels instead of relying on assertions.

### Fixed

- Append a newline after each explicit file log record.

## [0.1.0](https://github.com/duoncode/log/releases/tag/0.1.0) (2026-01-31)

Initial release.

### Added

- Simple PSR-3 compatible logger implementation
- SAPI and explicit file logging through PHP's `error_log`
- Configurable log levels and message formatting
