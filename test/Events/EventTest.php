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

    /**
     * @expectedException \LogicException
     */
    public function testIncorrectEventObject()
    {
        new Event(
            [
                'event_object' => TestClass\SimpleClass::class,
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Class don't exists: SomeClass
     */
    public function testIncorrectEventObjectFromNoneExistingClass()
    {
        new Event(
            [
                'event_object' => 'SomeClass',
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot create Event instance: Test\TestClass\SimpleClass
     */
    public function testCreateEventInstanceFromIncorrectObject()
    {
        new Event(
            [
                'event_object' => new \Test\TestClass\SimpleClass,
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );
    }

    //test create event object
    public function testCreateEventObject()
    {
        $event1 = new Event(
            [
                'event_object' => new EventDispatcher(),
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );
        $event2 = new Event(
            [
                'event_object' => EventDispatcher::class,
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );

        $this->assertInstanceOf(Event::class, $event1);
        $this->assertInstanceOf(Event::class, $event2);
    }
    


    public function testCallEvent()
    {
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

        $event = new Event(
            [
                'event_object' => new EventDispatcher(['events' => $eventConfig]),
                'event_config' => [],
            ],
            new Log(new SimpleLog)
        );

        $this->assertEmpty($testData);
        $event->callEvent('register_before_create', []);

        $this->assertNotEmpty($testData);
        $this->assertInstanceOf(EventDispatcher::class, $testData['register_before_create'][0]['event_object']);
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

        $simpleLog = new SimpleLog;
        $simpleLog->setOption('log_path', $logPath)
            ->setOption('level', 'debug');

        $log = new Log($simpleLog);

        $event = new Event(
            [
                'event_object' => new EventDispatcher(['events' => $eventConfig]),
                'event_config' => [],
            ],
            $log
        );

        $this->assertFileNotExists($logPath . self::REGISTER_LOG_NAME);

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
}
