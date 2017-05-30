<?php

namespace Test\TestClass;

class SimpleClass
{
    /**
     * @var array
     */
    public $constructorArgs = [];

    /**
     * SimpleClass constructor.
     *
     * @param int $arg1
     * @param int $arg2
     */
    public function __construct($arg1 = 0, $arg2 = 0)
    {
        $this->constructorArgs[] = $arg1;
        $this->constructorArgs[] = $arg2;
    }

    /**
     * @return int
     */
    public function testMe()
    {
        return 1;
    }

    /**
     * @param mixed $arg
     * @return mixed
     */
    public function testMeWithArgs($arg)
    {
        return $arg;
    }
}
