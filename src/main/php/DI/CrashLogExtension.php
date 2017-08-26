<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog\DI;

use Damejidlo\CrashLog\DefaultPathProvider;
use Damejidlo\CrashLog\FlysystemAdapter;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers;
use Nette\DI\ContainerBuilder;
use Nette\PhpGenerator\ClassType;



class CrashLogExtension extends CompilerExtension
{

	private $defaults = [
		'logger' => FlysystemAdapter::class,
		'logPathProvider' => [
			'service' => NULL,
			'defaults' => [
				'class' => DefaultPathProvider::class,
				'arguments' => ['/'],
			],
		],
		'filesystemService' => NULL,
		'delegateLoggerService' => NULL,
		'hookToTracy' => TRUE,
	];



	/**
	 * @inheritDoc
	 */
	public function loadConfiguration()
	{
		$config = Helpers::merge($this->getConfig(), $this->defaults);
		$builder = $this->getContainerBuilder();

		if ($config['delegateLoggerService'] === NULL) {
			$message = sprintf('Set %s to a Tracy\\ILogger service', $this->prefix('delegateLoggerService'));
			throw new \LogicException($message);
		};
		if ($config['filesystemService'] === NULL) {
			$message = sprintf('Set %s to a flysystem\'s FilesystemInterface service', $this->prefix('filesystemService'));
			throw new \LogicException($message);
		};

		$builder->addDefinition($this->prefix('logger'))
			->setClass($config['logger'])
			->setArguments([
				'@' . ltrim($config['delegateLoggerService'], '@'),
				$this->prepareExceptionPathService($config, $builder),
				'@' . ltrim($config['filesystemService'], '@'),
			])
			->setAutowired(FALSE);
	}



	/**
	 * @inheritdoc
	 */
	public function afterCompile(ClassType $class)
	{
		$config = Helpers::merge($this->getConfig(), $this->defaults);
		if ($config['hookToTracy'] === TRUE) {
			$initialize = $class->getMethod('initialize');

			$code = '\Tracy\Debugger::setLogger($this->getService(?));';
			$initialize->addBody($code, [$this->prefix('logger')]);
		}
	}



	/**
	 * @param array $config
	 * @param ContainerBuilder $builder
	 * @return string
	 */
	private function prepareExceptionPathService(array $config, ContainerBuilder $builder) : string
	{
		$exceptionPathService = $config['logPathProvider']['service'];
		if ($exceptionPathService === NULL) {
			$exceptionPathService = $this->prefix('logPathProvider');
			$builder->addDefinition($exceptionPathService)
				->setClass($config['logPathProvider']['defaults']['class'])
				->setArguments($config['logPathProvider']['defaults']['arguments']);
		}

		$serviceName = '@' . ltrim($exceptionPathService, '@');

		return $serviceName;
	}

}
