<?php

declare(strict_types=1);

namespace Duon\Log\Tests;

final class ErrorLog
{
	/** @var list<string> */
	private static array $messages = [];

	public static function clear(): void
	{
		self::$messages = [];
	}

	/** @return list<string> */
	public static function messages(): array
	{
		return self::$messages;
	}

	public static function write(
		string $message,
		int $messageType = 0,
		?string $destination = null,
	): bool {
		if ($messageType === 3 && is_string($destination)) {
			return file_put_contents($destination, $message, FILE_APPEND) !== false;
		}

		self::$messages[] = $message;

		return true;
	}
}
