<?php

declare(strict_types=1);

namespace BlueRegister\Events;

use BlueEvent\Event\Base\Interfaces\EventDispatcherInterface;
use BlueRegister\Log;

class Event
{
    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $event;

    /**
     * @var null|Log
     */
    protected ?Log $log;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param array $config
     * @param null|Log $log
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config, ?Log $log)
    {
        $this->log = $log;
        $this->config = $config;

        switch (true) {
            case $config['event_object'] instanceof EventDispatcherInterface:
                $this->event = $config['event_object'];
                break;

            case \is_string($config['event_object']):
                $this->classExists($config['event_object'])
                    ->createObjectFromString($config);
                break;

            default:
                $this->eventObjectException($config['event_object']);
        }
    }

    /**
     * @param array $config
     * @throws \LogicException
     */
    protected function createObjectFromString(array $config): void
    {
        $eventObject = new $config['event_object']($config['event_config']);

        if (!$eventObject instanceof EventDispatcherInterface) {
            $message = 'Event should be instance of '
                . EventDispatcherInterface::class
                . ': '
                . \get_class($eventObject);
            $this->makeLog($message);
            throw new \LogicException($message);
        }

        $this->event = $eventObject;
    }

    /**
     * @param object|string $eventObject
     * @throws \LogicException
     */
    protected function eventObjectException(object|string $eventObject): void
    {
        $object = 'unknown type';

        if (\is_object($eventObject)) {
            $object = \get_class($eventObject);
        }

        $message = 'Cannot create Event instance: ' . $object;
        $this->makeLog($message);
        throw new \LogicException($message);
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

    /**
     * @param string $name
     * @param array $data
     * @return $this
     */
    public function callEvent(string $name, array $data): self
    {
        $this->event->triggerEvent($name, $data + [$this->config]);
        $this->makeLog('Triggered: ' . $name);

        return $this;
    }
}
