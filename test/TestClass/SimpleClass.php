<?php

namespace Test\TestClass;

class SimpleClass
{
    public function __construct()
    {
        
    }
    
    public function testMe()
    {
        return 1;
    }

    public function testMeWithArgs($arg)
    {
        return $arg;
    }
}
