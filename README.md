# Duon Log

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6c5f675f8d914a88993f339a653ad6aa)](https://app.codacy.com/gh/duoncode/log/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/6c5f675f8d914a88993f339a653ad6aa)](https://app.codacy.com/gh/duoncode/log/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/duoncode/log/level.svg?)](https://duon.sh/log)
[![Psalm coverage](https://shepherd.dev/github/duoncode/log/coverage.svg?)](https://shepherd.dev/github/duoncode/log)

A simple PSR-3 logger using PHP's `error_log` function.

## Installation

```bash
composer require duon/log
```

## Usage

Create a logger without arguments to write to PHP's default SAPI error logger.

```php
use Duon\Log\Logger;
use Psr\Log\LogLevel;

$logger = new Logger();
$logger->info('Application started');
$logger->log(LogLevel::WARNING, 'Disk space is low');
```

Pass a file path to append log records to that file.

```php
use Duon\Log\Logger;

$logger = new Logger(__DIR__ . '/var/app.log');
$logger->error('Import failed');
```

Set `minimumLevel` to ignore records below a PSR-3 level.

```php
use Duon\Log\Logger;
use Psr\Log\LogLevel;

$logger = new Logger(minimumLevel: LogLevel::ERROR);

$logger->warning('Ignored');
$logger->error('Written');
```

Use a formatter when you want to interpolate context or append context values.

```php
use Duon\Log\Formatter\ContextFormatter;
use Duon\Log\Formatter\TemplateFormatter;
use Duon\Log\Logger;

$templateLogger = new Logger(formatter: new TemplateFormatter());
$templateLogger->info('User {id} logged in', ['id' => 42]);

$contextLogger = new Logger(formatter: new ContextFormatter());
$contextLogger->error('Import failed', ['file' => 'products.csv']);
```

Unknown levels throw `Psr\Log\InvalidArgumentException`.

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
