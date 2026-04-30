<?php

declare(strict_types=1);

namespace Duon\Log\Tests;

use DateTime;
use Duon\Log\Formatter\PlainFormatter;
use Duon\Log\Formatter\TextFormatter;
use ErrorException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use stdClass;

class FormatterTest extends TestCase
{
	#[TestDox('PlainFormatter returns the message and ignores context')]
	public function testPlainFormatter(): void
	{
		$formatter = new PlainFormatter();

		$this->assertEquals('Message', $formatter->format('Message'));
		$this->assertEquals('Message', $formatter->format('Message', ['test' => 'context']));
	}

	#[TestDox('TextFormatter interpolates placeholders and appends unused context')]
	public function testTextFormatter(): void
	{
		$template = 'String: {string}, Integer: {integer}';
		$context = [
			'string' => 'Scream Bloody Gore',
			'integer' => 13,
			'float' => 73.23,
			'datetime' => new DateTime('1987-05-25T13:31:23'),
			'array' => [13, 23, 71],
			'object' => new stdClass(),
			'other' => stream_context_create(),
			'null' => null,
			'exception' => new ErrorException('The test exception'),
		];

		$output = new TextFormatter()->format($template, $context);

		$this->assertStringContainsString('String: Scream Bloody Gore', $output);
		$this->assertStringContainsString('Integer: 13', $output);
		$this->assertStringNotContainsString('[string]', $output);
		$this->assertStringNotContainsString('[integer]', $output);
		$this->assertStringContainsString('[float] => 73.23', $output);
		$this->assertStringContainsString('[datetime] => 1987-05-25 13:31:23', $output);
		$this->assertStringContainsString('[array] => [Array [13,23,71]]', $output);
		$this->assertStringContainsString('[object] => [Instance of stdClass]', $output);
		$this->assertStringContainsString('[other] => [resource (stream-context)]', $output);
		$this->assertStringContainsString('[null] => [null]', $output);
		$this->assertStringContainsString('[exception] => ErrorException: The test exception', $output);
		$this->assertStringContainsString('#0', $output);
		$this->assertStringContainsString('FormatterTest->testTextFormatter', $output);
		$this->assertNotSame("\n", substr($output, -1));
	}

	#[TestDox('TextFormatter can omit exception tracebacks')]
	public function testTextFormatterWithoutTraceback(): void
	{
		$output = new TextFormatter(includeTraceback: false)->format('Error', [
			'exception' => new ErrorException('The test exception'),
		]);

		$this->assertStringContainsString('[exception] => ErrorException: The test exception', $output);
		$this->assertStringNotContainsString('#0', $output);
		$this->assertStringNotContainsString('FormatterTest->testTextFormatterWithoutTraceback', $output);
	}
}
