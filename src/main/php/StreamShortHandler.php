<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

use Monolog\Handler\StreamHandler;
use Tracy\Dumper;
use Tracy\Helpers;



class StreamShortHandler extends StreamHandler
{

	/**
	 * @param resource $stream
	 * @param mixed[] $record
	 */
	protected function streamWrite($stream, array $record)
	{
		$message = $record['extra']['exception'] ?? $record['message'];
		$record['formatted'] = $this->formatLogLine($message);

		parent::streamWrite($stream, $record);
	}



	/**
	 * Adapted from: Copyright (c) 2004, 2014 David Grudl (https://davidgrudl.com)
	 * All rights reserved. New BSD License - See tracy-license.md
	 *
	 * @param  string|\Throwable
	 * @return string
	 */
	private function formatLogLine($message) : string
	{
		if ($message instanceof \Throwable) {
			$dumpedMessage = $message->getMessage();
		} else {
			$dumpedMessage = Dumper::toText($message);
		}

		$pieces = [
			@date('[Y-m-d H-i-s]'), // @ timezone may not be set
			preg_replace('#\s*\r?\n\s*#', ' ', $dumpedMessage),
			' @  ' . Helpers::getSource(),
		];
		$logLine = implode(' ', $pieces);

		return $logLine;
	}

}
