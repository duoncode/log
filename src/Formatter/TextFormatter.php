<?php

declare(strict_types=1);

namespace Duon\Log\Formatter;

use Duon\Log\Formatter;
use Override;

/** @api */
final class TextFormatter implements Formatter
{
	use PreparesValue;

	public function __construct(
		private readonly bool $includeTraceback = true,
	) {}

	#[Override]
	public function format(string $message, array $context = []): string
	{
		if ($context === []) {
			return $message;
		}

		[$message, $context] = $this->interpolate($message, $context);

		if ($context === []) {
			return $message;
		}

		return $message . ":\n" . $this->transform($context);
	}

	/**
	 * @param array<array-key, mixed> $context
	 * @return array{string, array<array-key, mixed>}
	 */
	private function interpolate(string $message, array $context): array
	{
		$substitutes = [];

		foreach (array_keys($context) as $key) {
			$placeholder = '{' . $key . '}';

			if (!str_contains($message, $placeholder)) {
				continue;
			}

			$substitutes[$placeholder] = $this->prepare($context[$key], $this->includeTraceback);
			unset($context[$key]);
		}

		return [strtr($message, $substitutes), $context];
	}

	/** @param array<array-key, mixed> $context */
	private function transform(array $context): string
	{
		$result = '';

		foreach (array_keys($context) as $key) {
			$result .=
				"  [{$key}] => " . $this->prepare($context[$key], $this->includeTraceback, '      ') . "\n";
		}

		return $result;
	}
}
