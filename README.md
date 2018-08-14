CrashLog
====

Adapter for [Monolog](https://github.com/Seldaek/monolog) and
[Flysystem](https://flysystem.thephpleague.com/) filesystem abstraction.
All HTML dumps with uncaught exceptions or PHP errors (that Tracy generates)
are stored to any backend storage Flysystem supports. 

CrashLog requires a properly configured `FilesystemInterface` to operate.

Installation
----
```bash
composer.phar require damejidlo/crash-log
```

Configuration
----
There is no magic configuration anymore. Use standard monolog configuration.

Contributing
----
CrashLog is Open Source. Pull request are welcome. 
