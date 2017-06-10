<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use SimpleLog\Log;
use BlueRegister\Register;
use BlueRegister\Events\RegisterException;

class RegisterTest extends TestCase
{
    /**
     * name of test event log file
     */
    const REGISTER_LOG_NAME = '/register.log';

    /**
     * @var string
     */
    protected $logPath;

    /**
     * actions launched before test starts
     */
    protected function setUp()
    {
        $this->logPath = dirname(__FILE__) . '/log';

        if (file_exists($this->logPath . self::REGISTER_LOG_NAME)) {
            unlink($this->logPath . self::REGISTER_LOG_NAME);
        }
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

        $register->setConfig('log', false);
        $this->assertFalse($register->getConfig('log'));
    }

    public function testIncorrectLogObject()
    {
        $this->setExpectedException(
            'LogicException',
            'Log should be instance of SimpleLog\LogInterface: Test\TestClass\SimpleClass'
        );

        new Register(
            [
                'log' => true,
                'log_object' => '\Test\TestClass\SimpleClass',
            ]
        );
    }

    public function testIncorrectLogObjectFromNoneExistingClass()
    {
        $this->setExpectedException(
            'LogicException',
            'Class don\'t exists: SomeClass'
        );

        new Register([
            'log' => true,
            'log_object' => 'SomeClass',
        ]);
    }

    public function testCreateLogInstanceFromIncorrectObject()
    {
        $this->setExpectedException(
            'LogicException',
            'Cannot create Log instance: Test\TestClass\SimpleClass'
        );

        new Register([
            'log' => true,
            'log_object' => new \Test\TestClass\SimpleClass,
        ]);
    }

    public function testIncorrectEventObject()
    {
        $this->setExpectedException(
            'LogicException',
            'Event should be instance of'
            . ' \BlueEvent\Event\Base\Interfaces\EventDispatcherInterface: Test\TestClass\SimpleClass'
        );

        new Register([
            'events' => true,
            'event_object' => '\Test\TestClass\SimpleClass',
        ]);
    }

    public function testIncorrectEventObjectFromNoneExistingClass()
    {
        $this->setExpectedException(
            'LogicException',
            'Class don\'t exists: SomeClass'
        );

        new Register([
            'events' => true,
            'event_object' => 'SomeClass',
        ]);
    }

    public function testCreateEventInstanceFromIncorrectObject()
    {
        $this->setExpectedException(
            'LogicException',
            'Cannot create Event instance: Test\TestClass\SimpleClass'
        );

        new Register([
            'events' => true,
            'event_object' => new \Test\TestClass\SimpleClass,
        ]);
    }

    public function testInitRegisterWithGivenObjects()
    {
        new Register([
            'events' => true,
            'event_object' => new \BlueEvent\Event\Base\EventDispatcher,
            'log' => true,
            'log_object' => new \SimpleLog\Log,
        ]);
    }

    public function testFactoryForSimpleObject()
    {
        $register = new Register;

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals($register->getClassCounter(), ['\Test\TestClass\SimpleClass' => 1]);
        $this->assertEquals(1, $simpleClass->testMe());
        $this->assertEquals(
            $register->getRegisteredObjects(),
            ['\Test\TestClass\SimpleClass' => 'Test\TestClass\SimpleClass']
        );
    }

    public function testFactoryForSimpleObjectWithParameters()
    {
        $register = new Register();

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass', [1, 2]);

        $this->assertEquals([1, 2], $simpleClass->constructorArgs);
    }

    public function testFactoryWithNoneExistingOverride()
    {
        $this->setExpectedException(
            'LogicException',
            'Class don\'t exists: SomeClass'
        );

        $register = new Register;

        $register
            ->enableOverride()
            ->setOverrider('\Test\TestClass\SimpleClass', 'SomeClass')
            ->factory('\Test\TestClass\SimpleClass');
    }

    public function testFactoryWithOverrideOnlyOnce()
    {
        $register = new Register;

        $simpleClass = $register
            ->enableOverride()
            ->setOverrider('\Test\TestClass\SimpleClass', '\Test\TestClass\OverrideClass', true)
            ->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(2, $simpleClass->testMe());

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass2 = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(1, $simpleClass2->testMe());
    }

    public function testSingletonFactory()
    {
        $register = new Register();

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->singletonFactory('\Test\TestClass\SimpleClass', [1, 2]);
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory('\Test\TestClass\SimpleClass', [3, 4]);
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->getSingleton('\Test\TestClass\SimpleClass');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $register->destroySingleton('\Test\TestClass\SimpleClass');
        $simpleClass = $register->getSingleton('\Test\TestClass\SimpleClass');
        $this->assertEquals([0, 0], $simpleClass->constructorArgs);
    }

    public function testSingletonFactoryWithNames()
    {
        $register = new Register();

        $this->assertEquals($register->getClassCounter(), []);
        $this->assertEquals($register->getRegisteredObjects(), []);

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->singletonFactory('\Test\TestClass\SimpleClass', [1, 2], 'singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory('singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->getSingleton('singleton');
        $this->assertEquals([1, 2], $simpleClass->constructorArgs);

        $simpleClass = $register->singletonFactory('\Test\TestClass\SimpleClass', [3, 4]);
        $this->assertEquals([3, 4], $simpleClass->constructorArgs);

        $register->destroySingleton();
        $simpleClass = $register->singletonFactory('\Test\TestClass\SimpleClass');
        $this->assertEquals([0, 0], $simpleClass->constructorArgs);

        $this->setExpectedException('InvalidArgumentException', 'Class don\'t exists: singleton');
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
                        'object' => '\BlueRegister\Events\RegisterEvent',
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_before_create'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_after_create' => [
                        'object' => '\BlueRegister\Events\RegisterEvent',
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_after_create'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_before_return_singleton' => [
                        'object' => '\BlueRegister\Events\RegisterEvent',
                        'listeners' => [
                            function ($event) use (&$testData) {
                                /** @var $event \BlueRegister\Events\RegisterEvent */
                                $testData['register_before_return_singleton'] = $event->getEventParameters();
                            }
                        ]
                    ],
                    'register_class_dont_exists' => [
                        'object' => '\BlueRegister\Events\RegisterException',
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

        $register->factory('\Test\TestClass\SimpleClass', [1, 2]);

        $this->assertArrayHasKey('register_before_create', $testData);
        $this->assertEquals(
            ['\Test\TestClass\SimpleClass', [1, 2]],
            $testData['register_before_create']
        );

        $this->assertArrayHasKey('register_after_create', $testData);
        $this->assertTrue($testData['register_after_create'][0] instanceof \Test\TestClass\SimpleClass);

        $register->singletonFactory('\Test\TestClass\SimpleClass', [1, 2]);

        $this->assertArrayHasKey('register_before_return_singleton', $testData);
        $this->assertTrue($testData['register_before_return_singleton'][0] instanceof \Test\TestClass\SimpleClass);
        $this->assertEquals([1, 2], $testData['register_before_return_singleton'][1]);
        $this->assertEquals('\Test\TestClass\SimpleClass', $testData['register_before_return_singleton'][2]);

        try {
            $register->factory('SomeClass', [1, 2]);
        } catch (\InvalidArgumentException $exception) {
        }

        $this->assertArrayHasKey('register_class_dont_exists', $testData);
        $this->assertEquals('SomeClass', $testData['register_class_dont_exists'][0]);
    }

    public function testSystemEventWithKill()
    {
        RegisterException::allowKill(true);

        $this->assertTrue(RegisterException::isKillingAllowed());

        $register = new Register([
            'events' => true,
            'event_config' => [
                'events' => [
                    'register_class_dont_exists' => [
                        'object' => '\BlueRegister\Events\RegisterException',
                        'listeners' => []
                    ],
                ],
            ]
        ]);

        $this->setExpectedException(
            'RuntimeException',
            'System killed by Register Exception. Unknown class: SomeClass'
        );

        try {
            $register->factory('SomeClass');
        } catch (\InvalidArgumentException $exception) {
        }
    }

    public function testLogMessages()
    {
        $log = new Log;
        $log->setOption('log_path', $this->logPath);
        $log->setOption('type', 'register');

        $register = new Register([
            'log' => true,
            'log_object' => $log,
        ]);

        $register->factory('\Test\TestClass\SimpleClass');
        $this->assertFileExists($this->logFile());
        $this->clearLog();

        try {
            new Register(
                [
                    'log'          => true,
                    'log_object'   => $log,
                    'events'       => true,
                    'event_object' => '\Test\TestClass\SimpleClass'
                ]
            );
        } catch (\LogicException $exception) {
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
        $register->singletonFactory('\Test\TestClass\SimpleClass');
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
        $log->setOption('type', 'register');

        $register = new Register([
            'log' => true,
            'log_object' => $log,
        ]);

        $this->assertFalse($register->isOverrideEnable());

        $register->enableOverride();
        $this->assertTrue($register->isOverrideEnable());

        for ($i = 0; $i < 3; $i++) {
            $simpleClass = $register
                ->setOverrider('\Test\TestClass\SimpleClass', '\Test\TestClass\OverrideClass')
                ->factory('\Test\TestClass\SimpleClass');

            $this->assertFileExists($this->logFile());
            $this->clearLog();

            $this->assertEquals(2, $simpleClass->testMe());
        }

        $register->disableOverride();
        $this->assertFalse($register->isOverrideEnable());
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass');
        $this->assertEquals(1, $simpleClass->testMe());

        $simpleClass = $register
            ->enableOverride()
            ->factory('\Test\TestClass\SimpleClass');
        $this->assertEquals(2, $simpleClass->testMe());

        $simpleClass = $register
            ->unsetOverrider('\Test\TestClass\SimpleClass')
            ->factory('\Test\TestClass\SimpleClass');
        $this->assertEquals(1, $simpleClass->testMe());
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
