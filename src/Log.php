<?php

declare(strict_types=1);

namespace BlueRegister;

use SimpleLog\LogInterface;

class Log
{
    /**
     * @var null|LogInterface
     */
    protected ?LogInterface $log = null;

    /**
     * @param mixed $logObject
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(mixed $logObject)
    {
        switch (true) {
            case $logObject instanceof LogInterface:
                $this->log = $logObject;
                break;

            case \is_string($logObject):
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
    protected function createObjectFromString(string $logObject): void
    {
        $logObject = new $logObject();

        if (!$logObject instanceof LogInterface) {
            $message = 'Log should be instance of SimpleLog\LogInterface: ' . \get_class($logObject);
            throw new \LogicException($message);
        }
        
        $this->log = $logObject;
    }

    /**
     * @param mixed $logObject
     * @throws \LogicException
     */
    protected function logObjectException(mixed $logObject): void
    {
        $object = 'unknown type';

        if (\is_object($logObject)) {
            $object = \get_class($logObject);
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
    protected function classExists(string $namespace): self
    {
        if (!\class_exists($namespace)) {
            throw new \InvalidArgumentException('Class don\'t exists: ' . $namespace);
        }

        return $this;
    }

    /**
     * @param array|string $message
     * @return $this
     */
    public function makeLog(array|string $message): self
    {
        $this->log?->makeLog($message);

        return $this;
    }
}
