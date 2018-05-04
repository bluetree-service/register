<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use SimpleLog\Log as SimpleLog;
use BlueRegister\Log;

class LogTest extends TestCase
{
    /**
     * name of test event log file
     */
    const REGISTER_LOG_NAME = '/debug.log';

    /**
     * @var string
     */
    protected $logPath;

    /**
     * actions launched before test starts
     */
    protected function setUp()
    {
        $this->logPath = __DIR__ . '/log';

        $this->clearLog();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Log should be instance of SimpleLog\LogInterface: Test\TestClass\SimpleClass
     */
    public function testIncorrectLogObject()
    {
        new Log(TestClass\SimpleClass::class);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Class don't exists: SomeClass
     */
    public function testIncorrectLogObjectFromNoneExistingClass()
    {
        new Log('SomeClass');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot create Log instance: Test\TestClass\SimpleClass
     */
    public function testCreateLogInstanceFromIncorrectObject()
    {
        new Log(new \Test\TestClass\SimpleClass);
    }

    public function testCreateLogObject()
    {
        $log1 = new Log(new SimpleLog);
        $log2 = new Log(SimpleLog::class);

        $this->assertInstanceOf(Log::class, $log1);
        $this->assertInstanceOf(Log::class, $log2);
    }

    public function testLogMessages()
    {
        $this->clearLog();
        $simpleLog = new SimpleLog;
        $simpleLog->setOption('log_path', $this->logPath)
            ->setOption('level', 'debug');

        $log = new Log($simpleLog);
        $log->makeLog('Test log message');

        $this->assertFileExists($this->logFile());
        $this->clearLog();
    }

    /**
     * @return string
     */
    protected function logFile()
    {
        return $this->logPath . self::REGISTER_LOG_NAME;
    }

    protected function clearLog()
    {
        if (file_exists($this->logFile())) {
            unlink($this->logFile());
        }
    }

    /**
     * actions launched after test was finished
     */
    protected function tearDown()
    {
        $this->clearLog();
    }
}
