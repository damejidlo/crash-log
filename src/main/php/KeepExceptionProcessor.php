<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

class KeepExceptionProcessor
{

	/**
	 * @param mixed[] $record
	 * @return mixed[]
	 */
	public function __invoke(array $record) : array
	{
		if (isset($record['context']['exception'])
			&& ($record['context']['exception'] instanceof \Throwable)
		) {
			// exception passed to context
			$record['extra']['exception'] = $record['context']['exception'];
		}

		return $record;
	}

}
