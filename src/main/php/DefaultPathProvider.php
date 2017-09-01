<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

use Nette\SmartObject;



class DefaultPathProvider implements IExceptionPathProvider
{

	use SmartObject;

	/**
	 * @var string
	 */
	private $directory;



	/**
	 * @param string $directory
	 */
	public function __construct(string $directory)
	{
		$this->directory = $directory;
	}



	/**
	 * @inheritDoc
	 *
	 * Adapted from Tracy, Copyright (c) 2004, 2014 David Grudl (https://davidgrudl.com), All rights reserved.
	 * See src/main/resources/license/tracy-license.md
	 */
	public function getExceptionFile(\Throwable $exception) : string
	{
		$data = [];
		while ($exception !== NULL) {
			$data[] = [
				get_class($exception),
				$exception->getMessage(),
				$exception->getCode(),
				$exception->getFile(),
				$exception->getLine(),
				array_map(function ($item) {
					unset($item['args']);

					return $item;
				}, $exception->getTrace()),
			];
			$exception = $exception->getPrevious();
		}
		$hash = substr(md5(serialize($data)), 0, 10);
		$dir = strtr($this->directory . '/', '\\/', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);

		return sprintf('%sexception--%s.html', $dir, $hash);
	}

}
