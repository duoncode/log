---
title: Introduction
---
# Duon Log

!!! warning "Note"
    This library is under active development, some of the listed features are still experimental and subject to change. Large parts of the documentation are missing.

Duon Log is a small PSR-3 logger that writes through PHP's `error_log` function. Use it when you need a lightweight logger without Monolog-style handlers.

## Usage

Create a logger without arguments to write to PHP's default SAPI error logger.

```php
use Duon\Log\Logger;
use Psr\Log\LogLevel;

$logger = new Logger();
$logger->info('Application started');
$logger->log(LogLevel::WARNING, 'Disk space is low');
```

Pass a file path to append records to that file.

```php
$logger = new Logger(__DIR__ . '/var/app.log');
$logger->error('Import failed');
```

Set `minimumLevel` to ignore records below a PSR-3 level.

```php
$logger = new Logger(minimumLevel: LogLevel::ERROR);
```
