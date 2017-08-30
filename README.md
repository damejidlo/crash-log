CrashLog
====

Adapter for [Tracy](https://tracy.nette.org/en/) and
[Flysystem](https://flysystem.thephpleague.com/) filesystem abstraction.
All HTML dumps with uncaught exceptions or PHP errors that Tracy generates
are stored to any backend storage Flysystem supports. 

CrashLog requires a properly configured `FilesystemInterface` to operate.
All log messages that do not implement `\Throwable` are relayed
to the specified `ILogger` delegate. 

Installation
----
```bash
composer.phar require damejidlo/crash-log
```

Configuration
----
CrashLog may be used either standalone or in _Nette Framework_ using 
`CrashLogExtension`.

Nette integration is conveniently done in the main `config.neon`:
```yml
# register the extension
extensions:
	crashLog: Damejidlo\CrashLog\DI\CrashLogExtension

# configure the extension
crashLog:
	filesystemService: <service-name>
	delegateLoggerService: <service-name>

```

Contributing
----
CrashLog is Open Source. Pull request are welcome. 
