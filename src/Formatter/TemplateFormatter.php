<?php

declare(strict_types=1);

namespace Duon\Log\Formatter;

use Duon\Log\Formatter;
use Override;

/** @psalm-api */
class TemplateFormatter implements Formatter
{
	use PreparesValue;

	public function __construct(protected readonly bool $includeTraceback = true) {}

	#[Override]
	public function format(string $message, ?array $context): string
	{
		if ($context !== null && $context !== []) {
			return $this->interpolate($message, $context);
		}

		return $message;
	}

	protected function interpolate(string $template, array $context): string
	{
		$substitutes = [];

		foreach (array_keys($context) as $key) {
			$placeholder = '{' . $key . '}';

			if (strpos($template, $placeholder) === false) {
				continue;
			}

			$substitutes[$placeholder] = $this->prepare($context[$key], $this->includeTraceback);
		}

		$message = strtr($template, $substitutes);

		return $message;
	}
}
