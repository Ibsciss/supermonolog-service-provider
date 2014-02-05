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
			$level = MonologServiceProvider::translateLevel($app['monolog.level']);

			/* If debug mode, then returning a StreamHandler */
            if($app['debug'] || (isset($app['monolog.fingerscrossed']) && !$app['monolog.fingerscrossed'] && isset($app['monolog.rotatingfile']) && !$app['monolog.rotatingfile'])){
                return new StreamHandler($app['monolog.logfile'], $level);
            }

			/* If fingercrossed disabled */
			if(isset($app['monolog.fingerscrossed']) && !$app['monolog.fingerscrossed']) {
				return $app['monolog.fingerscrossed.handler'];	
			}

			/* If rotating file disabled */
			if(isset($app['monolog.rotatingfile']) && !$app['monolog.rotatingfile']) {
                return new FingersCrossedHandler(new StreamHandler($app['monolog.logfile'], $level), $Activationlevel);
			}

			return new FingersCrossedHandler($app['monolog.fingerscrossed.handler'], $Activationlevel);
        };

    }

    public function boot(Application $app){}
}
