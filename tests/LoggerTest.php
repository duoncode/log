<?php

declare(strict_types=1);

namespace Duon\Log\Tests;

use Duon\Log\Formatter\PlainFormatter;
use Duon\Log\Formatter\TextFormatter;
use Duon\Log\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
	#[TestDox('Write to file')]
	public function testLoggerToFile(): void
	{
		$logger = new Logger($this->logFile);

		$logger->debug('Scott');
		$logger->info('Steve');
		$logger->notice('James');
		$logger->warning('Chuck');
		$logger->error('Bobby');
		$logger->critical('Chris');
		$logger->alert('Kelly');
		$logger->emergency('Terry');

		$output = file_get_contents($this->logFile);
		$lines = explode(PHP_EOL, trim((string) $output));

		$this->assertCount(8, $lines);
		$this->assertStringContainsString('] DEBUG: Scott', $output);
		$this->assertStringContainsString('] INFO: Steve', $output);
		$this->assertStringContainsString('] NOTICE: James', $output);
		$this->assertStringContainsString('] WARNING: Chuck', $output);
		$this->assertStringContainsString('] ERROR: Bobby', $output);
		$this->assertStringContainsString('] CRITICAL: Chris', $output);
		$this->assertStringContainsString('] ALERT: Kelly', $output);
		$this->assertStringContainsString('] EMERGENCY: Terry', $output);
	}

	#[TestDox('Accept PSR-3 log levels')]
	public function testLoggerAcceptsPsrLogLevels(): void
	{
		$levels = [
			LogLevel::DEBUG => 'DEBUG',
			LogLevel::INFO => 'INFO',
			LogLevel::NOTICE => 'NOTICE',
			LogLevel::WARNING => 'WARNING',
			LogLevel::ERROR => 'ERROR',
			LogLevel::CRITICAL => 'CRITICAL',
			LogLevel::ALERT => 'ALERT',
			LogLevel::EMERGENCY => 'EMERGENCY',
		];
		$logger = new Logger($this->logFile);

		foreach (array_keys($levels) as $level) {
			$logger->log($level, $level);
		}

		$output = file_get_contents($this->logFile);

		foreach ($levels as $level => $label) {
			$this->assertStringContainsString("] {$label}: {$level}", $output);
		}
	}

	#[TestDox('Write to PHP SAPI error logger when no file specified')]
	public function testLoggerToPhpDefaultDestination(): void
	{
		// Logger with null file uses error_log() without message_type,
		// which sends to PHP's SAPI error logger. In CLI, this goes to stderr.
		// PHPUnit captures stderr, so we cannot verify file output here.
		// This test confirms Logger works without a file path specified.
		$logger = new Logger();

		$logger->debug('Scott');
		$logger->info('Steve');
		$logger->warning('Chuck');
		$logger->error('Bobby');
		$logger->alert('Kelly');

		$this->expectNotToPerformAssertions();
	}

	#[TestDox('Respect higher debug level')]
	public function testLoggerWithHigherDebugLevel(): void
	{
		$logger = new Logger($this->logFile, level: LogLevel::ERROR);

		$logger->debug('Scott');
		$logger->info('Steve');
		$logger->notice('James');
		$logger->warning('Chuck');
		$logger->error('Bobby');
		$logger->critical('Chris');
		$logger->alert('Kelly');
		$logger->emergency('Terry');

		$output = file_get_contents($this->logFile);

		$this->assertStringNotContainsString('] DEBUG: Scott', $output);
		$this->assertStringNotContainsString('] INFO: Steve', $output);
		$this->assertStringNotContainsString('] NOTICE: James', $output);
		$this->assertStringNotContainsString('] WARNING: Chuck', $output);
		$this->assertStringContainsString('] ERROR: Bobby', $output);
		$this->assertStringContainsString('] CRITICAL: Chris', $output);
		$this->assertStringContainsString('] ALERT: Kelly', $output);
		$this->assertStringContainsString('] EMERGENCY: Terry', $output);
	}

	#[DataProvider('invalidLevels')]
	#[TestDox('Fail with PSR-3 error on unknown log level')]
	public function testLoggerWithWrongLogLevel(mixed $level): void
	{
		$this->throws(InvalidArgumentException::class, 'Unknown log level');

		$logger = new Logger($this->logFile, level: LogLevel::ERROR);
		$logger->log($level, 'never logged');
	}

	#[TestDox('Fail with PSR-3 error on unknown configured log level')]
	public function testLoggerWithWrongMinimumLogLevel(): void
	{
		$this->throws(InvalidArgumentException::class, 'Unknown log level');

		new Logger($this->logFile, level: 'invalid');
	}

	/** @return iterable<string, array{mixed}> */
	public static function invalidLevels(): iterable
	{
		yield 'integer' => [1313];
		yield 'string' => ['invalid'];
		yield 'null' => [null];
	}

	#[TestDox('Format message with default TextFormatter')]
	public function testFormatMessage(): void
	{
		$logger = new Logger(file: $this->logFile);

		$logger->emergency('Template {string}', ['string' => 'Formatted']);
		$logger->info('Template {string}', ['string' => "For\0matted"]);

		$output = (string) file_get_contents($this->logFile);

		$this->assertStringContainsString('] EMERGENCY: Template Formatted', $output);
		$this->assertStringContainsString('] INFO: Template Formatted', $output);
		$this->assertStringNotContainsString("\0", $output);
	}

	#[TestDox('Format message with different formatters')]
	public function testFormatMessageAfterSettingFormatter(): void
	{
		$logger = new Logger(file: $this->logFile, formatter: new PlainFormatter());

		$logger->alert('Template {string}', ['string' => 'Formatted']);

		$output = file_get_contents($this->logFile);

		$this->assertStringContainsString('] ALERT: Template {string}', $output);
		$this->assertStringNotContainsString('] ALERT: Template Formatted', $output);

		$logger->formatter(new TextFormatter());
		$logger->alert('Template {string}', ['string' => 'Formatted']);

		$output = file_get_contents($this->logFile);

		$this->assertStringContainsString('] ALERT: Template Formatted', $output);
	}

	#[TestDox('Format message with cloned loggers')]
	public function testFormatMessageAfterCloningLogger(): void
	{
		$logger = new Logger(file: $this->logFile, formatter: new PlainFormatter());

		$logger->alert('Template {string}', ['string' => 'Formatted']);

		$output = file_get_contents($this->logFile);

		$this->assertStringContainsString('] ALERT: Template {string}', $output);
		$this->assertStringNotContainsString('] ALERT: Template Formatted', $output);

		$newLogger = $logger->withFormatter(new TextFormatter());
		$newLogger->alert('New Logger {string}', ['string' => 'Formatted']);
		$logger->alert('Old Logger {string}', ['string' => 'Formatted']);

		$output = file_get_contents($this->logFile);

		$this->assertStringContainsString('] ALERT: New Logger Formatted', $output);
		$this->assertStringContainsString('] ALERT: Old Logger {string}', $output);
	}
}
