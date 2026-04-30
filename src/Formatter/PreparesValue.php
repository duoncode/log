<?php

declare(strict_types=1);

namespace Duon\Log\Formatter;

use DateTimeInterface;
use Stringable;
use Throwable;

trait PreparesValue
{
	private function prepare(
		mixed $value,
		bool $includeTraceback,
		string $tracebackIndent = '',
	): string {
		return match (true) {
			// Exceptions must be first as they are Stringable
			$value instanceof Throwable => $this->getExceptionMessage(
				$value,
				$includeTraceback,
				$tracebackIndent,
			),
			is_scalar($value) || $value instanceof Stringable => (string) $value,
			$value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s T'),
			is_object($value) => '[Instance of ' . $value::class . ']',
			is_array($value) => $this->prepareArray($value),
			is_null($value) => '[null]',
			default => '[' . get_debug_type($value) . ']',
		};
	}

	/** @param array<array-key, mixed> $value */
	private function prepareArray(array $value): string
	{
		$encoded = json_encode($value, JSON_UNESCAPED_SLASHES);

		return '[Array ' . ($encoded !== false ? $encoded : '...') . ']';
	}

	private function getExceptionMessage(
		Throwable $exception,
		bool $includeTraceback,
		string $tracebackIndent,
	): string {
		$message = $exception::class . ': ' . $exception->getMessage();

		if ($includeTraceback) {
			$trace = $exception->getTraceAsString();

			if ($tracebackIndent) {
				// Indent each frame: split on '#', rejoin with indent+'#'
				$trace = implode($tracebackIndent . '#', explode('#', $trace));
			}

			$message .= "\n" . $trace;
		}

		return $message;
	}
}
