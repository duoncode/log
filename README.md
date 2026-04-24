# Duon Log

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6c5f675f8d914a88993f339a653ad6aa)](https://app.codacy.com/gh/duoncode/log/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/6c5f675f8d914a88993f339a653ad6aa)](https://app.codacy.com/gh/duoncode/log/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duoncode/log/level.svg?)](https://duon.sh/log)
[![Psalm coverage](https://shepherd.dev/github/duoncode/log/coverage.svg?)](https://shepherd.dev/github/duoncode/log)

A simple PSR-3 logger using PHP's `error_log` function.

## Testing

During testing, PHP's `error_log` ini setting is set to a temporary file. To print the output to
the console, prepend a special env variable to the PHPUnit cli command, as follows:

```bash
ECHO_LOG=1 phpunit
```

### Test Environment Requirements

Tests require:

- `ini_set()` function enabled (for `error_log` redirection)
- Writable system temp directory (for test log file isolation)
- PHP `error_reporting` must be modifiable

These are standard in development environments but may fail in restricted
PHP configurations where `ini_set` is disabled via `disable_functions`.

## License

This project is licensed under the [MIT license](LICENSE.md).
