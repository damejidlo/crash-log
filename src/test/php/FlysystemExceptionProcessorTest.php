<?php
declare(strict_types = 1);

require __DIR__ . '/bootstrap.php';

use Damejidlo\CrashLog\FlysystemExceptionProcessor;
use Damejidlo\CrashLog\IExceptionPathProvider;
use League\Flysystem\FilesystemInterface;
use Tester\Assert;
use Tester\TestCase;



class FlysystemExceptionProcessorTest extends TestCase
{

	private const PATH = '/some/where/error.html';



	/**
	 * @dataProvider provideRecord
	 * @param mixed[] $record
	 * @param bool $isWritten
	 * @param string|NULL $tracyPath
	 */
	public function testHandle(array $record, bool $isWritten, ?string $tracyPath) : void
	{
		$filesystem = Mockery::mock(FilesystemInterface::class);
		$pathProvider = Mockery::mock(IExceptionPathProvider::class);
		$pathProvider->shouldReceive('getExceptionFile')->andReturn(self::PATH);

		$handler = new FlysystemExceptionProcessor($filesystem, $pathProvider);

		if ($isWritten) {
			$filesystem->shouldReceive('write')->once();
		}

		$output = $handler->__invoke($record);
		if ($tracyPath === NULL) {
			Assert::false(isset($output['context']['tracy']));
		} else {
			Assert::same($tracyPath, $output['context']['tracy']);
		}
	}



	/**
	 * @return mixed[]
	 */
	protected function provideRecord() : array
	{
		$default = [
			'message' => 'It is broken',
			'level' => \Monolog\Logger::CRITICAL,
			'extra' => [],
			'tracyPath' => NULL,
		];

		return [
			[
				'record' => $default,
				'isWritten' => FALSE,
			],
			[
				'record' => $default + ['context' => ['exception' => new \Exception('Bla bla failed')]],
				'isWritten' => TRUE,
				'tracyPath' => self::PATH,
			],
		];
	}



	protected function tearDown() : void
	{
		Mockery::close();
	}

}



(new FlysystemExceptionProcessorTest())->run();
