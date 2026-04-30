<?php

declare(strict_types=1);

namespace Duon\Log\Formatter;

use DateTimeInterface;
use Stringable;
use Throwable;

trait PreparesValue
{
	public function prepare(mixed $value, bool $includeTraceback, string $tracebackIndent = ''): string
	{
		return match (true) {
			// Exceptions must be first as they are Stringable
			is_object($value) && is_subclass_of($value, Throwable::class) => $this->getExceptionMessage(
				$value,
				$includeTraceback,
				$tracebackIndent,
			),
			is_scalar($value) || is_object($value) && $value instanceof Stringable => (string) $value,
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

	protected function getExceptionMessage(
		Throwable $exception,
		bool $includeTraceback,
		string $tracebackIndent,
	): string {
		$message = $exception::class . ': ' . $exception->getMessage();

		if ($includeTraceback) {
			$trace = $exception->getTraceAsString();

			if ($tracebackIndent) {
				$trace = implode($tracebackIndent . '#', explode('#', $trace));
			}

			$message .= "\n" . $trace . "\n";
		}

		return $message;
	}
}
