<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use SimpleLog\Log;
use BlueRegister\Register;
use BlueRegister\Events\RegisterException;
use BlueEvent\Event\Base\EventDispatcher;
use BlueRegister\Events\RegisterEvent;

class RegisterTest extends TestCase
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

    public function testSetAndGetConfig()
    {
        $register = new Register();

        $this->assertFalse($register->getConfig('log'));
        $this->assertFalse($register->getConfig('events'));

        $register = new Register([
            'log' => true,
            'events' => true,
        ]);

        $this->assertTrue($register->getConfig('log'));
        $this->assertTrue($register->getConfig('events'));
        $this->assertEquals(
            [
                'log' => true,
                'events' => true,
                'log_object' => Log::class,
                'event_object' => EventDispatcher::class,
                'event_config' => []
            ],
            $register->getConfig()
        );

        $register->setConfig('log', false);
        $this->assertFalse($register->getConfig('log'));
    }

    public function testIncorrectLogObject()
    {
        $this->expectExceptionMessage("Log should be instance of SimpleLog\LogInterface: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

        new Register(
            [
                'log' => true,
                'log_object' => TestClass\SimpleClass::class,
            ]
        );
    }

    public function testIncorrectLogObjectFromNoneExistingClass()
    {
        $this->expectExceptionMessage("Class don't exists: SomeClass");
        $this->expectException(\LogicException::class);

        new Register([
            'log' => true,
            'log_object' => 'SomeClass',
        ]);
    }

    public function testCreateLogInstanceFromIncorrectObject()
    {
        $this->expectExceptionMessage("Cannot create Log instance: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

        new Register([
            'log' => true,
            'log_object' => new \Test\TestClass\SimpleClass,
        ]);
    }

    public function testIncorrectEventObject()
    {
        $this->expectException(\LogicException::class);

        new Register([
            'events' => true,
            'event_object' => TestClass\SimpleClass::class,
        ]);
    }

    public function testIncorrectEventObjectFromNoneExistingClass()
    {
        $this->expectExceptionMessage("Class don't exists: SomeClass");
        $this->expectException(\LogicException::class);

        new Register([
            'events' => true,
            'event_object' => 'SomeClass',
        ]);
    }

    public function testCreateEventInstanceFromIncorrectObject()
    {
        $this->expectExceptionMessage("Cannot create Event instance: Test\TestClass\SimpleClass");
        $this->expectException(\LogicException::class);

        new Register([
            'events' => true,
            'event_object' => new \Test\TestClass\SimpleClass,
        ]);
    }

    public function testInitRegisterWithGivenObjects()
    {
        $register = new Register([
            'events' => true,
            'event_object' => new \BlueEvent\Event\Base\EventDispatcher,
            'log' => true,
            'log_object' => new \SimpleLog\Log,
        ]);

        $this->assertInstanceOf(Register::class, $register);
    }

    public function testFactoryWithException()
    {
        $this->expectExceptionMessage("Test exception.");
        $this->expectException(\BlueRegister\RegisterException::class);

        $register = new Register;

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        $register->factory(TestClass\SimpleClass::class, [0, 0, true]);
    }

    public function testFactoryForSimpleObject()
    {
        $register = new Register;

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory(TestClass\SimpleClass::class);

        $this->assertEquals($register->getClassCounter(), [TestClass\SimpleClass::class => 1]);
        $this->assertEquals(1, $simpleClass->testMe());
        $this->assertEquals(
            $register->getRegisteredObjects(),
            [TestClass\SimpleClass::class => TestClass\SimpleClass::class]
        );
    }

    public function testFactoryForSimpleObjectWithParameters()
    {
        $register = new Register();

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory(TestClass\SimpleClass::class, [1, 2]);

        $this->assertEquals([1, 2], $simpleClass->constructorArgs);
    }

    public function testFactoryWithNoneExistingOverride()
    {
        $this->expectExceptionMessage("Class don't exists: SomeClass");
        $this->expectException(\LogicException::class);

        $register = new Register;

        $register
            ->enableOverride()
            ->setOverrider(TestClass\SimpleClass::class, 'SomeClass')
            ->factory(TestClass\SimpleClass::class);
    }

    public function testFactoryWithOverrideOnlyOnce()
    {
        $register = new Register;

        $simpleClass = $register
            ->enableOverride()
            ->setOverrider(TestClass\SimpleClass::class, TestClass\OverrideClass::class, true)
            ->factory(TestClass\SimpleClass::class);

        $this->assertEquals(2, $simpleClass->testMe());

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass2 = $register->factory(TestClass\SimpleClass::class);

        $this->assertEquals(1, $simpleClass2->testMe());
    }

    public function testSingletonFactory()
    {
        $register = new Register();

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->singletonFactory(TestClass\SimpleClass::class, [1, 2]);
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory(TestClass\SimpleClass::class, [3, 4]);
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->getSingleton(TestClass\SimpleClass::class);
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $register->destroySingleton(TestClass\SimpleClass::class);
        $simpleClass = $register->getSingleton(TestClass\SimpleClass::class);
        $this->assertEquals([0, 0], $simpleClass->constructorArgs);
    }

    public function testSingletonFactoryWithNames()
    {
        $this->expectExceptionMessage("Class don't exists: singleton");
        $this->expectException(\LogicException::class);
        
        $register = new Register();

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->singletonFactory(TestClass\SimpleClass::class, [1, 2], 'singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory('singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->getSingleton('singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory(TestClass\SimpleClass::class, [3, 4]);
        $this->assertEquals([3, 4], $simpleClass->constructorArgs);

        $register->destroySingleton();
        $simpleClass = $register->singletonFactory(TestClass\SimpleClass::class);
        $this->assertEquals([0, 0], $simpleClass->constructorArgs);

        //exception
        $register->getSingleton('singleton');
    }

    public function testFactoryEvents()
    {
        $testData = [];

        $register = new Register([
            'events' => true,
            'event_config' => [
                'events' => [
                    'register_before_create' => [
                        'object' => RegisterEvent::class,
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_before_create'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_after_create' => [
                        'object' => RegisterEvent::class,
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_after_create'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_before_return_singleton' => [
                        'object' => RegisterEvent::class,
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_before_return_singleton'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_class_dont_exists' => [
                        'object' => RegisterException::class,
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event RegisterException */
                                $testData['register_class_dont_exists'] = $event->getEventParameters();
                            }
                        ]
                    ],
                ],
            ]
        ]);

        $register->factory(TestClass\SimpleClass::class, [1, 2]);

        $this->assertArrayHasKey('register_before_create', $testData);
        $this->assertEquals(
            [TestClass\SimpleClass::class, [1, 2]],
            $testData['register_before_create']
        );

        $this->assertArrayHasKey('register_after_create', $testData);
        $this->assertInstanceOf(\Test\TestClass\SimpleClass::class, $testData['register_after_create'][0]);

        $register->singletonFactory(TestClass\SimpleClass::class, [1, 2]);

        $this->assertArrayHasKey('register_before_return_singleton', $testData);
        $this->assertInstanceOf(\Test\TestClass\SimpleClass::class, $testData['register_before_return_singleton'][0]);
        $this->assertEquals([1, 2], $testData['register_before_return_singleton'][1]);
        $this->assertEquals(TestClass\SimpleClass::class, $testData['register_before_return_singleton'][2]);

        try {
            $register->factory('SomeClass', [1, 2]);
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals('Class don\'t exists: SomeClass', $exception->getMessage());
        }

        $this->assertArrayHasKey('register_class_dont_exists', $testData);
        $this->assertEquals('SomeClass', $testData['register_class_dont_exists'][0]);
    }

    public function testSystemEventWithKill()
    {
        $this->expectExceptionMessage("System killed by Register Exception. Unknown class: SomeClass");
        $this->expectException(\RuntimeException::class);
        
        RegisterException::allowKill(true);

        $this->assertTrue(RegisterException::isKillingAllowed());

        $register = new Register([
            'events' => true,
            'event_config' => [
                'events' => [
                    'register_class_dont_exists' => [
                        'object' => RegisterException::class,
                        'listeners' => []
                    ],
                ],
            ]
        ]);

        try {
            $register->factory('SomeClass');
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals('Class don\'t exists: SomeClass', $exception->getMessage());
        }
    }

    public function testLogMessages()
    {
        $log = new Log;
        $log->setOption('log_path', $this->logPath);
        $log->setOption('level', 'debug');

        $register = new Register([
            'log' => true,
            'log_object' => $log,
        ]);

        $register->factory(TestClass\SimpleClass::class);
        $this->assertFileExists($this->logFile());
        $this->clearLog();

        try {
            new Register(
                [
                    'log'          => true,
                    'log_object'   => $log,
                    'events'       => true,
                    'event_object' => TestClass\SimpleClass::class
                ]
            );
        } catch (\LogicException $exception) {
            $message = 'Event should be instance of BlueEvent\Event\Base\Interfaces\EventDispatcherInterface:'
                . ' Test\TestClass\SimpleClass';
            $this->assertEquals($message, $exception->getMessage());
        }
        $this->assertFileExists($this->logFile());
        $this->clearLog();

        try {
            new Register(
                [
                    'log'          => true,
                    'log_object'   => $log,
                    'events'       => true,
                    'event_object' => new TestClass\SimpleClass
                ]
            );
        } catch (\LogicException $exception) {
            $this->assertEquals('Cannot create Event instance: Test\TestClass\SimpleClass', $exception->getMessage());
        }
        $this->assertFileExists($this->logFile());
        $this->clearLog();

        new Register(
            [
                'log'          => true,
                'log_object'   => $log,
                'events'       => true,
            ]
        );
        $register->singletonFactory(TestClass\SimpleClass::class);
        $this->assertFileExists($this->logFile());
        $this->clearLog();

        $register->destroySingleton();
        $this->assertFileExists($this->logFile());
        $this->clearLog();
    }

    public function testFactoryWithOverride()
    {
        $log = new Log;
        $log->setOption('log_path', $this->logPath);
        $log->setOption('level', 'debug');

        $register = new Register([
            'log' => true,
            'log_object' => $log,
        ]);

        $this->assertFalse($register->isOverrideEnable());

        $register->enableOverride();
        $this->assertTrue($register->isOverrideEnable());

        for ($i = 0; $i < 3; $i++) {
            $simpleClass = $register
                ->setOverrider(TestClass\SimpleClass::class, TestClass\OverrideClass::class)
                ->factory(TestClass\SimpleClass::class);

            $this->assertFileExists($this->logFile());
            $this->clearLog();

            $this->assertEquals(2, $simpleClass->testMe());
        }

        $register->disableOverride();
        $this->assertFalse($register->isOverrideEnable());
        $simpleClass = $register->factory(TestClass\SimpleClass::class);
        $this->assertEquals(1, $simpleClass->testMe());

        $simpleClass = $register
            ->enableOverride()
            ->factory(TestClass\SimpleClass::class);
        $this->assertEquals(2, $simpleClass->testMe());

        $simpleClass = $register
            ->unsetOverrider(TestClass\SimpleClass::class)
            ->factory(TestClass\SimpleClass::class);
        $this->assertEquals(1, $simpleClass->testMe());
        $this->assertFileExists($this->logFile());
        $this->clearLog();
    }

    public function testUnsetAllOverriders()
    {
        $register = new Register();

        $simpleClass = $register
            ->enableOverride()
            ->setOverrider(TestClass\SimpleClass::class, TestClass\OverrideClass::class)
            ->factory(TestClass\SimpleClass::class);
        $this->assertEquals(2, $simpleClass->testMe());

        $simpleClass = $register
            ->unsetOverrider()
            ->factory(TestClass\SimpleClass::class);
        $this->assertEquals(1, $simpleClass->testMe());
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
