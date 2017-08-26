<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

interface IExceptionPathProvider
{

	/**
	 * @param \Throwable $exception
	 * @return string
	 */
	public function getExceptionFile(\Throwable $exception) : string;

}
