<?php

declare(strict_types=1);

namespace Duon\Log\Tests;

use DateTime;
use Duon\Log\Formatter\ContextFormatter;
use Duon\Log\Formatter\MessageFormatter;
use Duon\Log\Formatter\TemplateFormatter;
use ErrorException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use stdClass;

class FormatterTest extends TestCase
{
	#[TestDox('Format message with MessageFormatter')]
	public function testMessageFormatter(): void
	{
		$formatter = new MessageFormatter();
		$output = $formatter->format('Message', null);

		$this->assertEquals('Message', $output);

		$output = $formatter->format('Message', ['test' => 'context']);

		$this->assertEquals('Message', $output);
	}

	#[TestDox('Format message with ContextFormatter')]
	public function testTemplateFormatter(): void
	{
		$template =
			'String: {string}, Integer: {integer}, '
			. 'DateTime: {datetime}, Array: {array}'
			. 'Float: {float}, Object: {object} '
			. 'Other: {other}, Null: {null}, {exception}';
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
			'missing' => 'should not be rendered',
		];

		$formatter = new TemplateFormatter();
		$output = $formatter->format($template, $context);

		$this->assertStringContainsString('String: Scream Bloody Gore', $output);
		$this->assertStringContainsString('Integer: 13', $output);
		$this->assertStringContainsString('Float: 73.23', $output);
		$this->assertStringContainsString('DateTime: 1987-05-25 13:31:23', $output);
		$this->assertStringContainsString('Array: [Array [13,23,71]]', $output);
		$this->assertStringContainsString('Object: [Instance of stdClass]', $output);
		$this->assertStringContainsString('Other: [resource (stream-context)]', $output);
		$this->assertStringContainsString('Null: [null]', $output);
		$this->assertStringContainsString('ErrorException: The test exception', $output);
		$this->assertStringNotContainsString('should not be rendered', $output);
		// Check if traceback exists
		$this->assertStringContainsString('#0', $output);
		$this->assertStringContainsString('FormatterTest->testTemplateFormatter', $output);

		$output = $formatter->format('Error', null);
		$this->assertEquals('Error', $output);

		$formatter = new TemplateFormatter(includeTraceback: false);
		$output = $formatter->format($template, $context);

		$this->assertStringContainsString('String: Scream Bloody Gore', $output);
		$this->assertStringNotContainsString('#0', $output);
		$this->assertStringNotContainsString('FormatterTest->testTemplateFormatter', $output);
	}

	#[TestDox('Format message with ContextFormatter')]
	public function testContextFormatter(): void
	{
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

		$formatter = new ContextFormatter();
		$output = $formatter->format('Error', $context);

		$this->assertStringContainsString('[string] => Scream Bloody Gore', $output);
		$this->assertStringContainsString('[integer] => 13', $output);
		$this->assertStringContainsString('[float] => 73.23', $output);
		$this->assertStringContainsString('[datetime] => 1987-05-25 13:31:23', $output);
		$this->assertStringContainsString('[array] => [Array [13,23,71]]', $output);
		$this->assertStringContainsString('[object] => [Instance of stdClass]', $output);
		$this->assertStringContainsString('[other] => [resource (stream-context)]', $output);
		$this->assertStringContainsString('[null] => [null]', $output);
		$this->assertStringContainsString('[exception] => ErrorException: The test exception', $output);
		// Check if traceback exists
		$this->assertStringContainsString('#0', $output);
		$this->assertStringContainsString('FormatterTest->testContextFormatter', $output);

		$output = $formatter->format('Error', null);
		$this->assertEquals('Error', $output);

		$formatter = new ContextFormatter(includeTraceback: false);
		$output = $formatter->format('Error', $context);

		$this->assertStringContainsString('[string] => Scream Bloody Gore', $output);
		$this->assertStringNotContainsString('#0', $output);
		$this->assertStringNotContainsString('FormatterTest->testContextFormatter', $output);
	}
}
