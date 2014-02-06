<?php

namespace Ibsciss\Tests\Silex\Provider;

use Silex\Application;

use Monolog\Logger;
use Monolog\TestCase;
use Monolog\Handler\TestHandler;

use Ibsciss\Silex\Provider\SuperMonologServiceProvider;

/**
 * Zend Soap Tests Cases
 */
class SuperMonologServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $dir = __DIR__.'/Fixtures';
        chmod($dir, 0777);
        if (!is_writable($dir))
            $this->markTestSkipped($dir.' must be writeable to test the RotatingFileHandler.');
    }

    public function testIfMonologIsLoaded()
    {
        $app = $this->getApplication();
        $this->assertInstanceOf('Monolog\Logger', $app['monolog']);
    }

    //default strategy is streamHandler inside fingercrossed
    public function testDefaultStrategies()
    {
        $app = $this->getApplication();
        $mainHandler = $app['monolog']->popHandler();
        $nestedHandler = \PHPUnit_Framework_Assert::readAttribute($mainHandler, 'handler');

        $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $mainHandler);
        $this->assertInstanceOf('Monolog\Handler\StreamHandler', $nestedHandler);
    }

    //debug strategy is streamHandler
    public function testDebugStrategy()
    {
        $app = $this->getApplication();
        $app['debug'] = true;

        $this->assertInstanceOf('Monolog\Handler\streamHandler', $app['monolog']->popHandler());
    }

    public function testSecondHandlerOverride()
    {
        $app = $this->getApplication();
        $app['monolog.fingerscrossed.handler'] = new TestHandler();

        $mainHandler = $app['monolog']->popHandler();
        $nestedHandler = \PHPUnit_Framework_Assert::readAttribute($mainHandler, 'handler');

        $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $mainHandler);
        $this->assertInstanceOf('Monolog\Handler\TestHandler', $nestedHandler);

    }
    //test activation.level
    public function testActivationLevel()
    {
        $test = new TestHandler();

        $app = $this->getApplication();
        $app['monolog.fingerscrossed.handler'] = $test;
        $app['monolog.fingerscrossed.level'] = Logger::WARNING;

        $handler = $app['monolog']->popHandler();
        $handler->handle($this->getRecord(Logger::DEBUG));
        $this->assertFalse($test->hasDebugRecords());
        $handler->handle($this->getRecord(Logger::WARNING));
        $this->assertTrue($test->hasDebugRecords());
        $this->assertTrue($test->hasWarningRecords());
    }

    public function testDisabledCrossedStrategy()
    {
         $app = $this->getApplication();
         $app['monolog.fingerscrossed'] = false;

         $handler = $app['monolog']->popHandler();
         $this->assertInstanceOf('Monolog\Handler\streamHandler', $handler);
    }

    public function testEnabledRotatingFileStrategy()
    {
         $app = $this->getApplication();
         $app['monolog.rotatingfile'] = true;

         $mainHandler = $app['monolog']->popHandler();
         $nestedHandler = \PHPUnit_Framework_Assert::readAttribute($mainHandler, 'handler');

         $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $mainHandler);
         $this->assertInstanceOf('Monolog\Handler\RotatingFileHandler', $nestedHandler);
    }

    public function testDisabledBFingersCrossedAndEnableRotatingFileStrategy()
    {
         $app = $this->getApplication();
         $app['monolog.rotatingfile'] = true;
         $app['monolog.fingerscrossed'] = false;

         $handler = $app['monolog']->popHandler();
         $this->assertInstanceOf('Monolog\Handler\RotatingFileHandler', $handler);
    }

    public function testMaxFilesInRotatingStrategy()
    {
        $app = $this->getApplication();
        $app['monolog.rotatingfile'] = true;
        $app['monolog.fingerscrossed'] = false;
        $app['monolog.rotatingfile.maxfiles'] = 7;

        $handler = $app['monolog']->popHandler();
        $maxfiles = \PHPUnit_Framework_Assert::readAttribute($handler, 'maxFiles');

        $this->assertEquals(7, $maxfiles);
    }

    public function testIfNormalizerFormatterIsLoadedByDefault()
    {
        $app = $this->getApplication();
        $handler = $app['monolog']->popHandler();
        $nestedHandler = \PHPUnit_Framework_Assert::readAttribute($handler, 'handler');
        $formatter = $nestedHandler->getFormatter();
        $this->assertInstanceOf('Monolog\Formatter\NormalizerFormatter', $formatter);
    }

    public function testIfNormalizerFormatterInDebug()
    {
        $app = $this->getApplication();
        $app['debug'] = true;
        $formatter = $app['monolog']->popHandler()->getFormatter();
        $this->assertInstanceOf('Monolog\Formatter\NormalizerFormatter', $formatter);
    }

    protected function getApplication()
    {
        $app = new Application();

        $app->register(new SuperMonologServiceProvider(),array(
            'monolog.logfile' => __DIR__.'/Fixtures/test.rot'
        ));

        return $app;
    }

     /**
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', $context = array())
    {
        return array(
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true))),
            'extra' => array(),
        );
    }

    public function tearDown()
    {
        foreach (glob(__DIR__.'/Fixtures/*.rot') as $file) {
            unlink($file);
        }
    }
}

?>
