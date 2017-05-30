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
}
