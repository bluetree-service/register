<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use BlueEvent\Event\Base\EventDispatcher;
use BlueRegister\Events\Event;
use BlueRegister\Events\RegisterEvent;
use BlueRegister\Log;
use SimpleLog\Log as SimpleLog;

class EventTest extends TestCase
{
    /**
     * name of test event log file
     */
    const REGISTER_LOG_NAME = '/debug.log';

    public function testIncorrectEventObject()
    {
        $this->expectException(\LogicException::class);

        $logPath = __DIR__ . '/log';
        $this->clearLog($logPath);

        new Event(
            [
                'event_object' => TestClass\SimpleClass::class,
                'event_config' => [],
            ],
            $this->createLogObject()
        );
    }

    public function testIncorrectEventObjectFromNoneExistingClass()
    {
        $this->expectExceptionMessage("Class don't exists: SomeClass");
        $this->expectException(\LogicException::class);

        $logPath = __DIR__ . '/log';
        $this->clearLog($logPath);

        new Event(
            [
                'event_object' => 'SomeClass',
                'event_config' => [],
            ],
            $this->createLogObject()
        );
    }

    public function testCreateEventInstanceFromIncorrectObject()
    {
        $this->expectExceptionMessage("Cannot create Event instance: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

        $logPath = __DIR__ . '/log';
        $this->clearLog($logPath);

        new Event(
            [
                'event_object' => new \Test\TestClass\SimpleClass,
                'event_config' => [],
            ],
            $this->createLogObject()
        );
    }

    public function testCreateEventObject()
    {
        $logPath = __DIR__ . '/log';
        $this->clearLog($logPath);
        $log = $this->createLogObject();

        $event1 = new Event(
            [
                'event_object' => new EventDispatcher(),
                'event_config' => [],
            ],
            $log
        );
        $event2 = new Event(
            [
                'event_object' => EventDispatcher::class,
                'event_config' => [],
            ],
            $log
        );

        $this->assertInstanceOf(Event::class, $event1);
        $this->assertInstanceOf(Event::class, $event2);
        $this->clearLog($logPath);
    }

    public function testCallEvent()
    {
        $logPath = __DIR__ . '/log';
        $testData = [];
        $eventConfig = [
            'register_before_create' => [
                'object' => RegisterEvent::class,
                'listeners' => [
                    function ($event) use (&$testData) {
                        /** @var $event \BlueRegister\Events\RegisterEvent */
                        $testData['register_before_create'] = $event->getEventParameters();
                    }
                ]
            ]
        ];

        $log = $this->createLogObject();

        $event = new Event(
            [
                'event_object' => new EventDispatcher(['events' => $eventConfig]),
                'event_config' => [],
            ],
            $log
        );

        $this->assertEmpty($testData);
        $event->callEvent('register_before_create', []);

        $this->assertNotEmpty($testData);
        $this->assertInstanceOf(EventDispatcher::class, $testData['register_before_create'][0]['event_object']);

        $this->clearLog($logPath);
    }

    public function testCallEventWithLog()
    {
        $logPath = __DIR__ . '/log';
        $eventConfig = [
            'register_before_create' => [
                'object' => RegisterEvent::class,
                'listeners' => []
            ]
        ];

        $this->clearLog($logPath);

        $log = $this->createLogObject();

        $event = new Event(
            [
                'event_object' => new EventDispatcher(['events' => $eventConfig]),
                'event_config' => [],
            ],
            $log
        );

        $this->assertFileDoesNotExist($logPath . self::REGISTER_LOG_NAME);

        $event->callEvent('register_before_create', []);

        $this->assertFileExists($logPath . self::REGISTER_LOG_NAME);
        $this->clearLog($logPath);
    }

    protected function clearLog($logPath)
    {
        $logFile = $logPath . self::REGISTER_LOG_NAME;

        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    /**
     * @return Log
     */
    protected function createLogObject()
    {
        $logPath = __DIR__ . '/log';

        $simpleLog = new SimpleLog;
        $simpleLog->setOption('log_path', $logPath)
            ->setOption('level', 'debug');

        return new Log($simpleLog);
    }
}
