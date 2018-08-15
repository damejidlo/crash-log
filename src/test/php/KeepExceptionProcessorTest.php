<?php
declare(strict_types = 1);

require __DIR__ . '/bootstrap.php';

use Damejidlo\CrashLog\KeepExceptionProcessor;
use Tester\Assert;
use Tester\TestCase;



class KeepExceptionProcessorTest extends TestCase
{

	public function testInvoke()
	{
		$record = [
			'message' => 'Foo',
			'context' => ['exception' => new \Exception('Bar')],
			'extra' => [],
		];

		$processor = new KeepExceptionProcessor();
		$actual = $processor->__invoke($record);

		Assert::same($record['context'], $actual['context']);
		Assert::same($record['context'], $actual['extra']);
	}

}



(new KeepExceptionProcessorTest())->run();
