<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Tracy\BlueScreen;



class FlysystemExceptionProcessor
{

	/**
	 * @var FilesystemInterface
	 */
	private $filesystem;

	/**
	 * @var IExceptionPathProvider
	 */
	private $pathProvider;

	/**
	 * @var bool[]
	 */
	private $processedExceptionFileNames = [];



	public function __construct(
		FilesystemInterface $filesystem,
		IExceptionPathProvider $pathProvider
	) {
		$this->filesystem = $filesystem;
		$this->pathProvider = $pathProvider;
	}



	/**
	 * Adapted from \Kdyby\Monolog\Diagnostics\MonologAdapter, authored by Martin Bažík <martin@bazo.sk> and Filip Procházka <filip@prochazka.su>
	 * All rights reserved. New BSD License - see kdyby-monolog-license.md
	 *
	 * @param mixed[] $record
	 * @return mixed[]
	 */
	public function __invoke(array $record) : array
	{
		if (isset($record['context']['tracy'])) {
			// already processed by MonologAdapter
			return $record;
		}

		if (isset($record['context']['exception'])
			&& ($record['context']['exception'] instanceof \Throwable)
		) {
			// exception passed to context
			$record['context']['tracy'] = $this->writeException($record['context']['exception']);
			unset($record['context']['exception']);
		}

		return $record;
	}



	private function writeException(\Throwable $value) : string
	{
		$path = $this->pathProvider->getExceptionFile($value);

		if (!isset($this->processedExceptionFileNames[$path])) {
			try {
				ob_start();
				(new BlueScreen())->render($value);
				$html = ob_get_contents();

				$this->filesystem->write($path, $html);
			} catch (FileExistsException $e) {
				// keep the first error report
			} finally {
				ob_end_clean();
			}
		}

		return $path;
	}

}
