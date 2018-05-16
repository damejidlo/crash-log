<?php
declare(strict_types = 1);

namespace Damejidlo\CrashLog\DI;

use Damejidlo\CrashLog\DefaultPathProvider;
use Damejidlo\CrashLog\FlysystemAdapter;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\PhpGenerator\ClassType;



class CrashLogExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
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
		'loggerServiceDelegate' => NULL,
		'hookToTracy' => TRUE,
		'hookedServiceOverride' => NULL,
	];



	/**
	 * @inheritDoc
	 */
	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		if ($config['loggerServiceDelegate'] === NULL) {
			$message = sprintf('Set %s to a Tracy\\ILogger service', $this->prefix('loggerServiceDelegate'));
			throw new \LogicException($message);
		};
		if ($config['filesystemService'] === NULL) {
			$message = sprintf('Set %s to a flysystem\'s FilesystemInterface service', $this->prefix('filesystemService'));
			throw new \LogicException($message);
		};

		$builder->addDefinition($this->prefix('logger'))
			->setClass($config['logger'])
			->setArguments([
				'@' . ltrim($config['loggerServiceDelegate'], '@'),
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
		$config = $this->getConfig();
		if ($config['hookToTracy'] === TRUE) {
			$initialize = $class->getMethod('initialize');

			if ($config['hookedServiceOverride'] === NULL) {
				$exposedService = $this->prefix('logger');
			} else {
				$exposedService = ltrim($config['hookedServiceOverride'], '@');
			}

			$code = '\Tracy\Debugger::setLogger($this->getService(?));';
			$initialize->addBody($code, [$exposedService]);
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
