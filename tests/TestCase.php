<?php

declare(strict_types=1);

namespace Duon\Log\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected ?int $defaultErrorReporting;
	protected mixed $defaultLog;
	protected mixed $tempFile;
	protected mixed $logFile;

	public function setUp(): void
	{
		// Capture output of error_log calls in a temporary file
		// to prevent it being printed to the console.
		$this->defaultLog = ini_get('error_log');
		$this->tempFile = tmpfile();

		if ($this->tempFile === false) {
			$this->markTestSkipped('Unable to create temp file for logging tests');
		}

		$this->logFile = stream_get_meta_data($this->tempFile)['uri'];
		ini_set('error_log', $this->logFile);
		ErrorLog::clear();

		$this->defaultErrorReporting = error_reporting();
		error_reporting(E_ALL);
	}

	public function tearDown(): void
	{
		// Read log content before cleanup
		$logFileContent = '';
		if ($this->logFile && is_file($this->logFile)) {
			$logFileContent = file_get_contents($this->logFile);
		}

		// Close tmpfile resource (auto-deletes the temp file)
		if ($this->tempFile && is_resource($this->tempFile)) {
			fclose($this->tempFile);
		}

		// Restore default error_log and error reporting
		ini_set('error_log', $this->defaultLog);
		error_reporting($this->defaultErrorReporting);

		$this->defaultErrorReporting = null;
		$this->defaultLog = null;
		$this->tempFile = null;
		$this->logFile = null;

		if (getenv('ECHO_LOG') && $logFileContent !== '') {
			error_log($logFileContent);
		}
	}

	public function throws(string $exception, ?string $message = null): void
	{
		$this->expectException($exception);

		if ($message) {
			$this->expectExceptionMessage($message);
		}
	}
}
