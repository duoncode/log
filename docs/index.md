---
title: Introduction
---
# Duon Log

Duon Log is a small PSR-3 logger that writes through PHP's `error_log` function. Use it when you need a lightweight logger without Monolog-style handlers.

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
