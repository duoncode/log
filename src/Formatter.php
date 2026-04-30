<?php

declare(strict_types=1);

namespace Duon\Log;

interface Formatter
{
	public function format(string $message, array $context = []): string;
}
