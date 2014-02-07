supermonolog-service-provider
=============================

An extended **monolog** service provider for **Silex framework**

[![Build Status](https://travis-ci.org/Ibsciss/supermonolog-service-provider.png?branch=master)](https://travis-ci.org/Ibsciss/supermonolog-service-provider) [![Coverage Status](https://coveralls.io/repos/Ibsciss/supermonolog-service-provider/badge.png?branch=master)](https://coveralls.io/r/Ibsciss/supermonolog-service-provider?branch=master)

## Why a **Super**MonologService ?

The silex built-in `MonologServiceProvider` use only a few of the monolog habilities, and don't allow advanced configuration.

This **SuperMonologService** is more extensible and provide two new logs strategies in the silex core :

* A `FingersCrossed` strategy
* A `RotatingFile` strategy

By default only the *FingersCrossed* strategy is activated, but you can use the strategies together or only once at time.

### What is FingerCrossed strategy ?

As described in the [Monolog documentation](https://github.com/Seldaek/monolog) :

> **FingersCrossedHandler:** A very interesting wrapper. It takes a logger as parameter and will accumulate log records of all levels until a record exceeds the defined severity level. At which point it delivers all records, including those of lower severity, to the handler it wraps. This means that until an error actually happens you will not see anything in your logs, but when it happens you will have the full information, including debug and info records. This provides you with all the information you need, but only when you need it.

### What is RotatingFile strategy ?

Like before, the Monolog documentation give us the answer :

> **RotatingFileHandler:** Logs records to a file and creates one logfile per day. It will also delete files older than `maxfiles`. You should use logrotate for high profile setups though, this is just meant as a quick and dirty solution.

## How to install it ?

1. Add `"Ibsciss/supermonolog-service-provider": "dev-master"` in the require section of your `composer.json` file and run the `composer update` command.
2. Register the service with the silex register method `$app->register(new SuperMonologServiceProvider())` (don't forget the use `\Ibsciss\Silex\Provider\SuperMonologServiceProvider` statement).

## Basic usage

SuperMonolog extends the original `monolog` service (and the associated `logger` service) by overriding the `monolog.handler` service to push is own strategy on it. So you can use your `$app['monolog']` service as before.

So you have to setup the `ServiceProvide` as if it was the built-in `monolog` service: you have to define where the log will stored by the application with the `monolog.logfile` attribute ([read full built-in provider doc](http://silex.sensiolabs.org/doc/providers/monolog.html)).

```php
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/development.log',
));
```

After the `SuperMonologService` registration, only the `FingersCrossed` strategy is activated (and only in production mode).

In debug mode (`$app['debug'] = true;`) the service provide a basic `StreamHandler`.

*You can disable the `FingersCrossed` strategy with the `monolog.fingerscrossed` attribute set to `false` and come back to the `StreamHandler` used by the default `MonologServiceProvider`.*

## FingersCrossedHandler options

With SuperMonologService, the default handler use a FingersCrossed strategy to handle logs, its provide the following options :

* `monolog.fingerscrossed` a boolean value enabling the fingerscrossed strategy (default: **true**)
* `monolog.fingerscrossed.handler` the internal handler used by the fingerscrossed strategy to send logs (default: **StreamHandler**)
* `monolog.fingerscrossed.level` the severity level wich the logs are printed to the defined handler, accept :
    * `Monolog\Logger::DEBUG`
    * `Monolog\Logger::INFO`
    * `Monolog\Logger::NOTICE` (default level)
    * `Monolog\Logger::WARNING`
    * `Monolog\Logger::ERROR`
    * `Monolog\Logger::CRITICAL`
    * `Monolog\Logger::ALERT`
    * `Monolog\Logger::EMERGENCY`

*Note : the `monolog.level` is still available and used to define from wich logging level the logs are handled. For example if you define the `monolog.level` to `INFO` and the `monolog.fingerscrossed.level` to `WARNING`, all log from the INFO severity level are sended if a WARNING event occur*

## RotatingFile strategy

You can enable `RotatingFileHandler` by setting the `monolog.rotatingfile` attribute to `true`.

*By default the FingersCrossedHanfler` is remains active and the RotatingFileHandler is used as an internal handler called by the FingersCrossedHandler to send logs.*

If the `monolog.fingerscrossed` is set to `false` and the `monolog.rotatingfile` to `true` the default handler became a `RotatingFile` one.

The rotatingFile strategy define the following options :

* `monolog.rotatingfile` a boolean value enabling the rotating file strategy (default: `false`)
* `monolog.rotatingfile.maxfiles` an integer value, set the maximum files keep by the rotating handler.
