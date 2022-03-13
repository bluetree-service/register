<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use SimpleLog\Log as SimpleLog;
use BlueRegister\Log;
use Test\TestClass;

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
    protected function setUp(): void
    {
        $this->logPath = __DIR__ . '/log';

        $this->clearLog();
    }

    public function testIncorrectLogObject()
    {
        $this->expectExceptionMessage("Log should be instance of SimpleLog\LogInterface: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

        new Log(TestClass\SimpleClass::class);
    }

    public function testIncorrectLogObjectFromNoneExistingClass()
    {
        $this->expectExceptionMessage("Class don't exists: SomeClass");
        $this->expectException(\LogicException::class);

        new Log('SomeClass');
    }

    public function testCreateLogInstanceFromIncorrectObject()
    {
        $this->expectExceptionMessage("Cannot create Log instance: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

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
    protected function tearDown(): void
    {
        $this->clearLog();
    }
}
