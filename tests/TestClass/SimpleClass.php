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
     * @param bool $exception
     * @throws \LogicException
     */
    public function __construct($arg1 = 0, $arg2 = 0, $exception = false)
    {
        if ($exception) {
            throw new \LogicException('Test exception.');
        }

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
}
