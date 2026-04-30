---
title: Introduction
---

# Duon Log

Duon Log is a small PSR-3 logger for simple applications and libraries.

It intentionally covers the simple case: PSR-3 messages written through PHP's `error_log` with lightweight text formatting. If you need handlers, channels, processors, log rotation, structured JSON logs, buffering, remote transports, or complex filtering, use [Monolog](https://seldaek.github.io/monolog/) or another full logging library instead.

## Installation

```bash
composer require duon/log
```

## Default SAPI logging

Create a logger without arguments to write to PHP's default SAPI error logger.

```php
use Duon\Log\Logger;
use Psr\Log\LogLevel;

$logger = new Logger();
$logger->info('Application started');
$logger->log(LogLevel::WARNING, 'Disk space is low');
```

## File logging

Pass a file path to append log records to that file.

```php
use Duon\Log\Logger;

$logger = new Logger(__DIR__ . '/var/app.log');
$logger->error('Import failed');
```

## Level filtering

Set `level` to ignore records below a PSR-3 level.

```php
use Duon\Log\Logger;
use Psr\Log\LogLevel;

$logger = new Logger(level: LogLevel::ERROR);

$logger->warning('Ignored');
$logger->error('Written');
```

Unknown levels throw `Psr\Log\InvalidArgumentException`.

## Formatters

A formatter receives the log message and PSR-3 context and returns the text that is written after the timestamp and level.

### TextFormatter

`TextFormatter` is the default. It interpolates matching `{key}` placeholders, appends unused context values, and includes exception tracebacks by default.

```php
use Duon\Log\Formatter\TextFormatter;
use Duon\Log\Logger;

$logger = new Logger(formatter: new TextFormatter());

$logger->info('User {id} logged in', ['id' => 42]);
// User 42 logged in

$logger->error('Import failed', ['file' => 'products.csv']);
// Import failed:
//   [file] => products.csv
```

Disable exception tracebacks when you only want the exception class and message.

```php
$logger = new Logger(formatter: new TextFormatter(includeTraceback: false));
```

### PlainFormatter

`PlainFormatter` returns the message unchanged and ignores context. Use it when you want full control over the message text.

```php
use Duon\Log\Formatter\PlainFormatter;
use Duon\Log\Logger;

$logger = new Logger(formatter: new PlainFormatter());
$logger->info('User {id} logged in', ['id' => 42]);
// User {id} logged in
```
