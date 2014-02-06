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
        //parent service provider activation
        $app->register(new MonologServiceProvider());

        //define default options
        $app['monolog.fingerscrossed.level'] = Logger::NOTICE;
        $app['monolog.fingerscrossed'] = true;
        $app['monolog.rotatingfile'] = false;
        $app['monolog.rotatingfile.maxfiles'] = 10;
        $app['monolog.fingerscrossed.handler'] = function() use ($app){
            $level = MonologServiceProvider::translateLevel($app['monolog.level']);
            return new StreamHandler($app['monolog.logfile']);
        };

        //main override function
        $app['monolog.handler'] = function() use ($app){

            //setup level
			$Activationlevel = MonologServiceProvider::translateLevel($app['monolog.fingerscrossed.level']);
			$level = MonologServiceProvider::translateLevel($app['monolog.level']);

            //debug mode
			if($app['debug'])
                return (isset($app['monolog.handler.debug'])) ?
                    $app['monolog.handler.debug'] :
				    new StreamHandler($app['monolog.logfile'], $level);

            //if rotatingfile enable : figerscrossedHandler override
            if($app['monolog.rotatingfile'])
                $app['monolog.fingerscrossed.handler'] = new RotatingFileHandler(
                    $app['monolog.logfile'],
                    $app['monolog.rotatingfile.maxfiles'],
                    $level
                );

            //apply default strategy
			return ($app['monolog.fingerscrossed']) ?
                new FingersCrossedHandler($app['monolog.fingerscrossed.handler'], $Activationlevel) :
                $app['monolog.fingerscrossed.handler'];
        };

    }

    public function boot(Application $app){}
}
