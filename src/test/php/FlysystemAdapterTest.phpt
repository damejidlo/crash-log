<?php

namespace Damejidlo\CrashLog;

require __DIR__ . '/bootstrap.php';

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Tracy\ILogger;



/**
 * @testCase
 */
class FlysystemAdapterTest extends TestCase
{

	public function testNonException()
	{
		$delegate = Mockery::mock(ILogger::class);
		$delegate->shouldReceive('log')->with('foo', Mockery::any())->once();

		$logger = new FlysystemAdapter(
			$delegate,
			Mockery::mock(IExceptionPathProvider::class),
			Mockery::mock(FilesystemInterface::class)
		);

		Assert::noError(function () use ($logger) {
			$logger->log('foo');
		});
	}



	public function testFallbackLogging()
	{
		$loggingException = new \LogicException();
		$logMessage = new \RuntimeException();

		$delegate = Mockery::mock(ILogger::class);
		$delegate->shouldReceive('log')->with($logMessage, nonEmptyString())->once();
		$delegate->shouldReceive('log')->with($loggingException, nonEmptyString())->once();

		$pathProvider = Mockery::mock(IExceptionPathProvider::class);
		$pathProvider->shouldReceive('getExceptionFile')->andThrow($loggingException);

		$logger = new FlysystemAdapter(
			$delegate,
			$pathProvider,
			Mockery::mock(FilesystemInterface::class)
		);

		Assert::noError(function () use ($logger, $logMessage) {
			$logger->log($logMessage);
		});
	}



	/**
	 * @dataProvider happyPathExceptionProvider
	 * @param \Throwable|NULL $exception
	 */
	public function testHappyPath(\Throwable $exception = NULL)
	{
		$exceptionFilePath = '/bar/exception-123.html';

		$pathProvider = Mockery::mock(IExceptionPathProvider::class);
		$pathProvider->shouldReceive('getExceptionFile')->andReturn($exceptionFilePath);

		$filesystem = Mockery::mock(FilesystemInterface::class);
		if ($exception === NULL) {
			$filesystem->shouldReceive('write')->with($exceptionFilePath, nonEmptyString());
		} else {
			$filesystem->shouldReceive('write')
				->with($exceptionFilePath, nonEmptyString())
				->andThrow($exception);
		}

		$logger = new FlysystemAdapter(
			Mockery::mock(ILogger::class),
			$pathProvider,
			$filesystem
		);

		Assert::noError(function () use ($logger) {
			$logger->log(new \RuntimeException());
		});
	}



	/**
	 * @return array
	 */
	protected function happyPathExceptionProvider() : array
	{
		return [
			[NULL],
			[new FileExistsException('/bar/exception-123.html')],
		];
	}



	protected function tearDown()
	{
		Mockery::close();
	}

}



(new FlysystemAdapterTest())->run();
