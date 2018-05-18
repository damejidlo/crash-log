<?php

namespace Damejidlo\CrashLog;

require __DIR__ . '/bootstrap.php';

use Tester\Assert;
use Tester\TestCase;



/**
 * @testCase
 */
class LogPathProviderTest extends TestCase
{

	public function testGetExceptionFile() : void
	{
		$exception = new \RuntimeException();

		$logPathProvider = new DefaultPathProvider('/foo/bar');
		$exceptionFile = $logPathProvider->getExceptionFile($exception);

		Assert::same($exceptionFile, $logPathProvider->getExceptionFile($exception), 'Unstable exception name');
		Assert::match('/foo/bar/exception--%h%.html', $exceptionFile);
	}

}



(new LogPathProviderTest())->run();
