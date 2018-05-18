<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

use Tracy\Dumper;
use Tracy\Helpers;
use Tracy\ILogger;



class StreamShortLogger implements ILogger
{

	/**
	 * @var ILogger
	 */
	private $delegate;

	/**
	 * @var string
	 */
	private $destination;



	public function __construct(ILogger $delegate, string $destination)
	{
		$this->delegate = $delegate;
		$this->destination = $destination;
	}



	public function log($value, $priority = self::INFO) : void
	{
		$logLine = $this->formatLogLine($value) . PHP_EOL;
		file_put_contents($this->destination, $logLine, FILE_APPEND);
		$this->delegate->log($value, $priority);
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
