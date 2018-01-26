<?php

namespace BlueRegister;

use BlueRegister\Events\Event;
use BlueEvent\Event\Base\EventDispatcher;
use SimpleLog\Log as SimpleLog;

class Register
{
    /**
     * @var array
     */
    protected $config = [
        'log' => false,
        'events' => false,
        'log_object' => SimpleLog::class,
        'event_object' => EventDispatcher::class,
        'event_config' => [],
    ];

    /**
     * store information of all called by register objects
     *
     * @var array
     */
    protected $registeredObjects = [];

    /**
     * store list of objects called as singletons
     *
     * @var array
     */
    protected $singletons = [];

    /**
     * store list of overrides
     *
     * @var array
     */
    protected $overrides = [];

    /**
     * store information about number of created objects
     *
     * @var array
     */
    protected $classCounter = [];

    /**
     * @var bool
     */
    protected $allowOverride = false;

    /**
     * @var null|\BlueRegister\Events\Event
     */
    protected $event;

    /**
     * @var null|\BlueRegister\Log
     */
    protected $log;

    /**
     * Register constructor. Allow to set config on create
     *
     * @param array $config
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        if ($this->config['log'] === true) {
            $this->log = new Log($this->config['log_object']);
        }

        if ($this->config['events'] === true) {
            $this->event = new Event($this->config, $this->log);
        }
    }

    /**
     * create new instance of given class
     *
     * @param string $namespace
     * @param array $args
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function factory($namespace, array $args = [])
    {
        $namespace = $this->checkOverrider($namespace);

        $this->classExists($namespace)
            ->callEvent('register_before_create', [$namespace, $args]);

        //try / catch
        $object = new $namespace(...$args);

        $this->callEvent('register_after_create', [$object]);

        $this->setClassCounter($namespace);
        $this->registeredObjects[$namespace] = get_class($object);

        $this->makeLog([
            'Object created: ' . $namespace . '. With args:',
            $args
        ]);

        return $object;
    }

    /**
     * create singleton instance of given class
     *
     * @param string $namespace
     * @param array $args
     * @param null|string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function singletonFactory($namespace, array $args = [], $name = null)
    {
        if (is_null($name)) {
            $name = $namespace;
        }

        if (!isset($this->singletons[$name])) {
            $this->singletons[$name] = $this->factory($namespace, $args);
        }

        $this->callEvent(
            'register_before_return_singleton',
            [$this->singletons[$name], $args, $name]
        );

        return $this->singletons[$name];
    }

    /**
     * check that given class should be replaced by some other
     *
     * @param string $namespace
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function checkOverrider($namespace)
    {
        if ($this->allowOverride && isset($this->overrides[$namespace])) {
            $oldNamespace = $namespace;

            $namespace = $this->overrides[$namespace]['overrider'];
            $this->classExists($namespace);

            if ($this->overrides[$oldNamespace]['only_once']) {
                $this->unsetOverrider($oldNamespace);
            }
        }

        return $namespace;
    }

    /**
     * get all configuration or only specified key
     *
     * @param null|string $key
     * @return array|mixed
     */
    public function getConfig($key = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return $this->config[$key];
    }

    /**
     * set config parameter
     *
     * @param string $key
     * @param mixed $val
     * @return $this
     */
    public function setConfig($key, $val)
    {
        $this->config[$key] = $val;
        return $this;
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
            $this->callEvent('register_class_dont_exists', [$namespace]);

            throw new \InvalidArgumentException('Class don\'t exists: ' . $namespace);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $data
     * @return $this
     */
    protected function callEvent($name, array $data)
    {
        if (!is_null($this->event)) {
            $this->event->callEvent($name, $data);
        }

        return $this;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    protected function makeLog($message)
    {
        if (!is_null($this->log)) {
            $this->log->makeLog($message);
        }

        return $this;
    }

    /**
     * destroy called object
     *
     * @param string $name
     * @return $this
     */
    public function destroySingleton($name = null)
    {
        if (is_null($name)) {
            $this->singletons = [];
        }

        if (isset($this->singletons[$name])) {
            unset($this->singletons[$name]);
        }

        $this->makeLog('Destroy singleton: ' . (is_null($name) ? 'all' : $name));

        return $this;
    }

    /**
     * get called object
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getSingleton($name)
    {
        return $this->singletonFactory($name);
    }

    /**
     * return all registered objects by their references
     *
     * @return array
     */
    public function getRegisteredObjects()
    {
        return $this->registeredObjects;
    }

    /**
     * return list of created by Loader::getClass objects and number of executions
     *
     * @return array
     */
    public function getClassCounter()
    {
        return $this->classCounter;
    }

    /**
     * increment by 1 class execution
     *
     * @param string $class
     * @return Register
     */
    protected function setClassCounter($class)
    {
        if (!isset($this->classCounter[$class])) {
            $this->classCounter[$class] = 0;
        }

        ++$this->classCounter[$class];
        return $this;
    }

    /**
     * set class that be called instead of given in factory or singleton (override is disabled by default)
     *
     * @param string $namespace
     * @param string $overrider
     * @param bool $onlyOnce
     * @return $this
     */
    public function setOverrider($namespace, $overrider, $onlyOnce = false)
    {
        $this->overrides[$namespace] = [
            'overrider' => $overrider,
            'only_once' => (bool)$onlyOnce,
        ];

        $this->makeLog(
            'Override set for: '
            . $namespace
            . ', to: '
            . $overrider
            . '. Once: '
            . ($onlyOnce ? 'true' : 'false')
        );

        return $this;
    }

    /**
     * disable given or all overriders
     *
     * @param null|string $namespace
     * @return $this
     */
    public function unsetOverrider($namespace = null)
    {
        if (is_null($namespace)) {
            $this->overrides = [];
        } else {
            unset($this->overrides[$namespace]);
        }

        $this->makeLog('Override unset for: ' . $namespace);

        return $this;
    }

    /**
     * enable overriding
     *
     * @return $this
     */
    public function enableOverride()
    {
        $this->allowOverride = true;
        return $this;
    }

    /**
     * disable overriding
     *
     * @return $this
     */
    public function disableOverride()
    {
        $this->allowOverride = false;
        return $this;
    }

    /**
     * return information that overriding is enabled
     *
     * @return bool
     */
    public function isOverrideEnable()
    {
        return $this->allowOverride;
    }
}
