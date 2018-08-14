<?php
declare(strict_types = 1);

require __DIR__ . '/bootstrap.php';

use Damejidlo\CrashLog\StreamShortHandler;
use Tester\Assert;
use Tester\FileMock;
use Tester\TestCase;



class StreamShortHandlerTest extends TestCase
{

	/**
	 * @dataProvider provideRecord
	 *
	 * @param mixed[] $record
	 * @param string $expectation
	 */
	public function testHandle(array $record, string $expectation)
	{
		$logFile = FileMock::create();

		$handler = new StreamShortHandler($logFile);

		$handler->handle($record);

		Assert::match($expectation, file_get_contents($logFile));
	}



	protected function provideRecord() : array
	{
		return [
			[
				'record' => [
					'message' => 'It is broken',
					'level' => \Monolog\Logger::CRITICAL,
					'extra' => [],
				],
				'expectation' => '',
			],
			[
				'record' => [
					'message' => 'Máš to rozbitý',
					'context' => ['exception' => new \Exception('Bla bla failed')],
					'level' => \Monolog\Logger::CRITICAL,
					'extra' => [],
				],
				'expectation' => '%a% Bla bla failed  @  CLI (PID: %a%): %a%/src/test/php/StreamShortHandlerTest.php',
			],
		];
	}



	protected function tearDown() : void
	{
		Mockery::close();
	}

}



(new StreamShortHandlerTest())->run();
