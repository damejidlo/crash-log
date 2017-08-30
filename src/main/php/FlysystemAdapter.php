<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Nette\SmartObject;
use Tracy\BlueScreen;
use Tracy\ILogger;



class FlysystemAdapter implements ILogger
{

	use SmartObject;

	/**
	 * @var ILogger
	 */
	private $delegate;

	/**
	 * @var IExceptionPathProvider
	 */
	private $pathProvider;

	/**
	 * @var FilesystemInterface
	 */
	private $filesystem;



	/**
	 * @param ILogger $delegate
	 * @param FilesystemInterface $filesystem
	 * @param IExceptionPathProvider $pathProvider
	 */
	public function __construct(
		ILogger $delegate,
		IExceptionPathProvider $pathProvider,
		FilesystemInterface $filesystem
	) {
		$this->delegate = $delegate;
		$this->pathProvider = $pathProvider;
		$this->filesystem = $filesystem;
	}



	/**
	 * @inheritdoc
	 */
	public function log($value, $priority = self::INFO)
	{
		if ($value instanceof \Throwable) {
			try {
				ob_start();
				(new BlueScreen())->render($value);
				$html = ob_get_contents();

				$path = $this->pathProvider->getExceptionFile($value);
				$this->filesystem->write($path, $html);
			} catch (FileExistsException $e) {
				// keep the first error report
			} catch (\Throwable $e) {
				$this->delegate->log($value, $priority);
				$this->delegate->log($e, ILogger::ERROR);
			} finally {
				ob_end_clean();
			}
		} else {
			$this->delegate->log($value, $priority);
		}
	}

}
