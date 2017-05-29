<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use BlueRegister\Register;

class RegisterTest extends TestCase
{
    public function testFactoryForSimpleObject()
    {
        $register = new Register();

        /** @var \Test\TestClass\SimpleClass $simpleClass */
        $simpleClass = $register->factory('\Test\TestClass\SimpleClass');

        $this->assertEquals(1, $simpleClass->testMe());
    }
}
