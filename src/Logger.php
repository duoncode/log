<?php

declare(strict_types=1);

namespace Duon\Log;

use Duon\Log\Formatter\TextFormatter;
use Override;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface as PsrLogger;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Stringable;

/** @api */
final class Logger implements PsrLogger
{
	use LoggerTrait;

	public const string DEBUG = LogLevel::DEBUG;
	public const string INFO = LogLevel::INFO;
	public const string NOTICE = LogLevel::NOTICE;
	public const string WARNING = LogLevel::WARNING;
	public const string ERROR = LogLevel::ERROR;
	public const string CRITICAL = LogLevel::CRITICAL;
	public const string ALERT = LogLevel::ALERT;
	public const string EMERGENCY = LogLevel::EMERGENCY;

	private const int ERROR_LOG_APPEND_TO_FILE = 3;

	/** @var array<string, positive-int> */
	private const array LEVEL_SEVERITY = [
		self::DEBUG => 100,
		self::INFO => 200,
		self::NOTICE => 300,
		self::WARNING => 400,
		self::ERROR => 500,
		self::CRITICAL => 600,
		self::ALERT => 700,
		self::EMERGENCY => 800,
	];

	/** @var array<string, non-empty-string> */
	private const array LEVEL_LABELS = [
		self::DEBUG => 'DEBUG',
		self::INFO => 'INFO',
		self::NOTICE => 'NOTICE',
		self::WARNING => 'WARNING',
		self::ERROR => 'ERROR',
		self::CRITICAL => 'CRITICAL',
		self::ALERT => 'ALERT',
		self::EMERGENCY => 'EMERGENCY',
	];

	protected Formatter $formatter;

	public function __construct(
		protected ?string $file = null,
		protected string $level = self::DEBUG,
		?Formatter $formatter = null,
	) {
		$this->formatter = $formatter ?? new TextFormatter();
		$this->level = $this->validateLevel($level);
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
		$level = $this->validateLevel($level);

		if (self::LEVEL_SEVERITY[$level] < self::LEVEL_SEVERITY[$this->level]) {
			return;
		}

		$message = $this->formatter->format((string) $message, $context);
		$message = str_replace("\0", '', $message);
		$time = date('Y-m-d H:i:s D T');
		$line = "[{$time}] " . self::LEVEL_LABELS[$level] . ": {$message}";

		if (is_string($this->file)) {
			error_log($line . PHP_EOL, self::ERROR_LOG_APPEND_TO_FILE, $this->file);

			return;
		}

		error_log($line);
	}

	/** @return key-of<self::LEVEL_SEVERITY> */
	private function validateLevel(mixed $level): string
	{
		if (is_string($level) && array_key_exists($level, self::LEVEL_SEVERITY)) {
			return $level;
		}

		throw new InvalidArgumentException('Unknown log level: ' . $this->printLevel($level));
	}

	private function printLevel(mixed $level): string
	{
		if (is_scalar($level) || $level instanceof Stringable) {
			return (string) $level;
		}

		return get_debug_type($level);
	}
}
