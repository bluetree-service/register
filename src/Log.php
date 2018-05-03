<?php

namespace BlueRegister;

use \SimpleLog\LogInterface;

class Log
{
    /**
     * @var null|LogInterface
     */
    protected $log;

    /**
     * @param mixed $logObject
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct($logObject)
    {
        switch (true) {
            case $logObject instanceof LogInterface:
                $this->log = $logObject;
                break;

            case is_string($logObject):
                $this->classExists($logObject)
                    ->createObjectFromString($logObject);
                break;

            default:
                $this->logObjectException($logObject);
        }
    }

    /**
     * @param string $logObject
     * @throws \LogicException
     */
    protected function createObjectFromString($logObject)
    {
        $this->log = new $logObject();

        if (!$this->log instanceof LogInterface) {
            $message = 'Log should be instance of SimpleLog\LogInterface: ' . get_class($this->log);
            throw new \LogicException($message);
        }
    }

    /**
     * @param object $logObject
     * @throws \LogicException
     */
    protected function logObjectException($logObject)
    {
        $object = 'unknown type';

        if (is_object($logObject)) {
            $object = get_class($logObject);
        }

        throw new \LogicException('Cannot create Log instance: ' . $object);
    }

    /**
     * check that class exists and throw exception if not
     *
     * @param string $namespace
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function classExists($namespace)
    {
        if (!class_exists($namespace)) {
            throw new \InvalidArgumentException('Class don\'t exists: ' . $namespace);
        }

        return $this;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    public function makeLog($message)
    {
        if (!is_null($this->log)) {
            $this->log->makeLog($message);
        }

        return $this;
    }
}
