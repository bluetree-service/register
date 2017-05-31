<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use BlueRegister\Register;

class RegisterTest extends TestCase
{
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
            'Event should be instance of BlueEvent\Event\Base\EventDispatcher: Test\TestClass\SimpleClass'
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
        $register = new Register();

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(1, $simpleClass->testMe());
    }

    public function testFactoryForSimpleObjectWithParameters()
    {
        $register = new Register();

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass', [1, 2]);

        $this->assertEquals([1, 2], $simpleClass->constructorArgs);
    }

    public function testFactoryWithOverride()
    {
        $register = new Register();

        for ($i = 0; $i < 3; $i++) {
            $simpleClass = $register
                ->setOverrider('\Test\TestClass\SimpleClass', '\Test\TestClass\OverrideClass')
                ->factory('\Test\TestClass\SimpleClass');

            $this->assertEquals(2, $simpleClass->testMe());
        }

        $register->unsetOverrider('\Test\TestClass\SimpleClass');
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(1, $simpleClass->testMe());
    }

    public function testFactoryWithNoneExistingOverride()
    {
        $this->setExpectedException(
            'LogicException',
            'Class don\'t exists: SomeClass'
        );

        $register = new Register();

        $register->setOverrider('\Test\TestClass\SimpleClass', 'SomeClass')
            ->factory('\Test\TestClass\SimpleClass');
    }

    public function testFactoryWithOverrideOnlyOnce()
    {
        $register = new Register();

        $simpleClass = $register
            ->setOverrider('\Test\TestClass\SimpleClass', '\Test\TestClass\OverrideClass', true)
            ->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(2, $simpleClass->testMe());

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass2 = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(1, $simpleClass2->testMe());
    }
}
