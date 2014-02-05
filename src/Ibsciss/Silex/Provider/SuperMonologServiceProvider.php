<?php
namespace Ibsciss\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;

use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SuperMonologServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['monolog.fingerscrossed.level'] = Logger::NOTICE;

        $app->register(new MonologServiceProvider());

        $app['monolog.fingerscrossed.handler'] = function() use ($app){
            $level = MonologServiceProvider::translateLevel($app['monolog.level']);

            return new RotatingFileHandler($app['monolog.logfile'], 5, $level);
        };

        $app['monolog.handler'] = function() use ($app){
            $Activationlevel = MonologServiceProvider::translateLevel($app['monolog.fingerscrossed.level']);

            if($app['debug']){
                $level = MonologServiceProvider::translateLevel($app['monolog.level']);
                return new StreamHandler($app['monolog.logfile'], $level);
            }

            return new FingersCrossedHandler(
                $app['monolog.fingerscrossed.handler'],
                $Activationlevel
            );
        };

    }

    public function boot(Application $app){}
}