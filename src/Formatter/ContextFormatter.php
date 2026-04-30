<?php

declare(strict_types=1);

namespace Duon\Log\Formatter;

use Duon\Log\Formatter;
use Override;

/** @psalm-api */
class ContextFormatter implements Formatter
{
	use PreparesValue;

	public function __construct(
		protected readonly bool $includeTraceback = true,
	) {}

	#[Override]
	public function format(string $message, ?array $context): string
	{
		if ($context !== null && $context !== []) {
			return $message . ":\n" . $this->transform($context);
		}

		return $message;
	}

	protected function transform(array $context): string
	{
		$result = '';

		foreach (array_keys($context) as $key) {
			$result .=
				"  [{$key}] => " . $this->prepare($context[$key], $this->includeTraceback, '      ') . "\n";
		}

		return $result;
	}
}
