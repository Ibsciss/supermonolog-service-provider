supermonolog-service-provider
=============================

An extended monolog service provider for Silex framework

## Why a **Super**MonologService ?

The basic monolog service provider use only a few of the monolog habilities. This service provider offer a more advanced default configuration, and many power options.

## Install

1. Add `"Ibsciss/supermonolog-service-provider": "dev-master"` in the require section of your `composer.json` file and run the `composer update` command.
2. Register the service with the silex register method `$app->register(new SuperMonologServiceProvider())` (don't forget the use `\Ibsciss\Silex\Provider\SuperMonologServiceProvider` statement).

## Basic usage

SuperMonolog extends the original `monolog` service (and the associated `logger` service). In fact SuperMonolog override the `monolog.handler` service to push is own strategy on it.

## FingersCrossedHandler strategy

With SuperMonologService, the default handler use a FingersCrossed strategy to handle logs.

* `monolog.fingerscrossed.handler`
* `monolog.fingerscrossed.level`

### Disabled FingersCrossed

You can disable it by setting the `monolog.fingerscrossed` attribute to `false`, in this case only the rotation strategy remain.
*if both fingerscrossed and rotation strategies are disabled, the default strategy is set to a stream handler.*

## RotatingFile strategy

### Disabled RotatingFile

You can disable it by setting the `monolog.rotatingfile` attribute to `false`, in this case the `FingersCrossedHandler` remain but with `StreamHandler` as internal handler.
*if both fingerscrossed and rotation strategies are disabled, the default strategy is set to a stream handler.*

## Debug mode

In debug mode, the SuperMonologService is disabled and the classical `StreamHandler`is used instead.