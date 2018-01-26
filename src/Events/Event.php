<?php

namespace BlueRegister\Events;

use BlueEvent\Event\Base\Interfaces\EventDispatcherInterface;

class Event
{
    /**
     * @var \BlueEvent\Event\Base\Interfaces\EventDispatcherInterface
     */
    protected $event;

    /**
     * @var null|\BlueRegister\Log
     */
    protected $log;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     * @param \BlueRegister\Log $log
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config, $log)
    {
        $this->log = $log;
        $this->config = $config;

        switch (true) {
            case $config['event_object'] instanceof EventDispatcherInterface:
                $this->event = $config['event_object'];
                break;

            case is_string($config['event_object']) && $this->classExists($config['event_object']):
                $this->event = new $config['event_object']($config['event_config']);

                if (!$this->event instanceof EventDispatcherInterface) {
                    $message = 'Event should be instance of ' . EventDispatcherInterface::class . ': '
                        . get_class($this->event);
                    $this->makeLog($message);
                    throw new \LogicException($message);
                }

                break;

            default:
                $message = 'Cannot create Event instance: ' . get_class($config['event_object']);
                $this->makeLog($message);
                throw new \LogicException($message);

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

    /**
     * @param string $name
     * @param array $data
     * @return $this
     */
    public function callEvent($name, array $data)
    {
        if (!is_null($this->event)) {
            $this->event->triggerEvent($name, $data + [$this->config]);
            $this->makeLog('Triggered: ' . $name);
        }

        return $this;
    }
}
