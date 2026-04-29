<?php

declare(strict_types=1);

namespace Duon\Log;

use Duon\Log\Formatter\MessageFormatter;
use Override;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface as PsrLogger;
use Stringable;

/** @psalm-api */
class Logger implements PsrLogger
{
	public const int DEBUG = 100;
	public const int INFO = 200;
	public const int NOTICE = 300;
	public const int WARNING = 400;
	public const int ERROR = 500;
	public const int CRITICAL = 600;
	public const int ALERT = 700;
	public const int EMERGENCY = 800;

	/** @var array<int, non-empty-string> */
	protected array $levelLabels;

	public function __construct(
		protected ?string $logfile = null,
		protected ?Formatter $formatter = null,
		protected int $minimumLevel = self::DEBUG,
	) {
		if (!$formatter) {
			$this->formatter = new MessageFormatter();
		}

		$this->levelLabels = [
			self::DEBUG => 'DEBUG',
			self::INFO => 'INFO',
			self::NOTICE => 'NOTICE',
			self::WARNING => 'WARNING',
			self::ERROR => 'ERROR',
			self::CRITICAL => 'CRITICAL',
			self::ALERT => 'ALERT',
			self::EMERGENCY => 'EMERGENCY',
		];
	}

	public function formatter(Formatter $formatter): void
	{
		$this->formatter = $formatter;
	}

	public function withFormatter(Formatter $formatter): self
	{
		$new = clone $this;
		$new->formatter($formatter);

		return $new;
	}

	#[Override]
	public function log(
		mixed $level,
		string|Stringable $message,
		array $context = [],
	): void {
		$message = (string) $message;
		assert(is_int($level) || is_numeric($level));
		$level = (int) $level;

		if ($level < $this->minimumLevel) {
			return;
		}

		if (isset($this->levelLabels[$level])) {
			$levelLabel = $this->levelLabels[$level];
		} else {
			throw new InvalidArgumentException('Unknown log level: ' . (string) $level);
		}

		assert(!is_null($this->formatter));
		$message = $this->formatter->format(str_replace("\0", '', $message), $context);
		$time = date('Y-m-d H:i:s D T');
		$line = "[{$time}] {$levelLabel}: {$message}";

		if (is_string($this->logfile)) {
			error_log($line, 3, $this->logfile);
		} else {
			error_log($line);
		}
	}

	#[Override]
	public function debug(string|Stringable $message, array $context = []): void
	{
		$this->log(self::DEBUG, $message, $context);
	}

	#[Override]
	public function info(string|Stringable $message, array $context = []): void
	{
		$this->log(self::INFO, $message, $context);
	}

	#[Override]
	public function notice(string|Stringable $message, array $context = []): void
	{
		$this->log(self::NOTICE, $message, $context);
	}

	#[Override]
	public function warning(string|Stringable $message, array $context = []): void
	{
		$this->log(self::WARNING, $message, $context);
	}

	#[Override]
	public function error(string|Stringable $message, array $context = []): void
	{
		$this->log(self::ERROR, $message, $context);
	}

	#[Override]
	public function critical(string|Stringable $message, array $context = []): void
	{
		$this->log(self::CRITICAL, $message, $context);
	}

	#[Override]
	public function alert(string|Stringable $message, array $context = []): void
	{
		$this->log(self::ALERT, $message, $context);
	}

	#[Override]
	public function emergency(string|Stringable $message, array $context = []): void
	{
		$this->log(self::EMERGENCY, $message, $context);
	}
}
