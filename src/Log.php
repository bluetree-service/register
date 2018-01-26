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

            case is_string($logObject) && $this->classExists($logObject):
                $this->log = new $logObject();

                if (!$this->log instanceof LogInterface) {
                    $message = 'Log should be instance of SimpleLog\LogInterface: ' . get_class($this->log);
                    throw new \LogicException($message);
                }

                break;

            default:
                throw new \LogicException('Cannot create Log instance: ' . get_class($logObject));
                break;
        }
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
