<?php

namespace BlueRegister;

class Register
{
    /**
     * @var array
     */
    protected $config = [
        'log' => false,
        'events' => false,
        'log_object' => 'SimpleLog\Log',
        'event_object' => 'BlueEvent\Event\Base\EventDispatcher',
        'event_config' => [],
    ];

    /**
     * store information of all called by register objects
     *
     * @var array
     */
    protected $registeredObjects = [];

    /**
     * 
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
     * @var null|\BlueEvent\Event\Base\Interfaces\EventDispatcherInterface
     */
    protected $event = null;

    /**
     * @var null|\SimpleLog\LogInterface
     */
    protected $log = null;

    /**
     * create new instance of given class
     *
     * @param string $namespace
     * @param array $args
     * @param array $config
     * @return mixed
     */
    public function factory($namespace, array $args = [], array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $namespace = $this->checkOverrider($namespace);

        $this->classExists($namespace)
            ->callEvent('register_before_create', [$namespace, $args]);

        $object = call_user_func_array($namespace, $args);

        $this->callEvent('register_after_create', [$object]);

        if ($object) {
            $this->setClassCounter($namespace);
            $this->registeredObjects[$namespace] = get_class($object);
        }

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
     * @param array $config
     * @return mixed
     */
    public function singletonFactory($namespace, array $args = [], $name = null, array $config = [])
    {
        if (!is_null($name)) {
            $name = $namespace;
        }

        if (isset($this->singletons[$name])) {
            $this->callEvent(
                'register_before_return_singleton',
                [$this->singletons[$name], $args, $name]
            );

            return $this->singletons[$name];
        }

        return $this->factory($namespace, $args, $config);
    }

    /**
     * check that given class should be replaced by some other
     * 
     * @param string $namespace
     * @return string
     */
    protected function checkOverrider($namespace)
    {
        if (isset($this->overrides[$namespace])) {
            $namespace = $this->overrides[$namespace]['overrider'];
            $this->classExists($namespace);

            if (isset($this->overrides[$namespace]['only_once'])) {
                $this->unsetOverrider($namespace);
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
        if ($this->config['events'] === true) {
            if (is_null($this->event)) {
                $this->registerEvent();
            }

            $this->event->triggerEvent($name, $data + [$this->config]);

            $this->makeLog('Triggered: ' . $name);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function registerEvent()
    {
        switch (true) {
            case $this->config['events'] === false;
                break;

            case $this->config['event_object'] instanceof \BlueEvent\Event\Base\EventDispatcher:
                $this->event = $this->config['event_object'];
                break;

            default:
                $this->event = new $this->config['event_object']($this->config['event_config']);

                if (!$this->event instanceof \BlueEvent\Event\Base\EventDispatcher) {
                    $this->makeLog('Event should be instance of BlueEvent\Event\Base\EventDispatcher');
                    throw new \LogicException('Event should be instance of BlueEvent\Event\Base\EventDispatcher');
                }

                break;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function registerLog()
    {
        switch (true) {
            case $this->config['log'] === false;
                break;

            case $this->config['log_object'] instanceof \SimpleLog\LogInterface:
                $this->log = $this->config['log_object'];
                break;

            default:
                $this->log = new $this->config['log_object'];

                if (!$this->event instanceof \SimpleLog\LogInterface) {
                    $this->makeLog('Event should be instance of SimpleLog\LogInterface');
                    throw new \LogicException('Event should be instance of SimpleLog\LogInterface');
                }

                break;
        }

        return $this;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    protected function makeLog($message)
    {
        if ($this->config['log'] === true) {
            if (is_null($this->log)) {
                $this->registerLog();
            }

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
    public function destroySingleton($name)
    {
        if (isset($this->singletons[$name])) {
            unset($this->singletons[$name]);
        }

        $this->makeLog('Destroy singleton: ' . $name);

        return $this;
    }

    /**
     * get called object
     *
     * @param string $name
     * @return mixed
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
    public function setClassCounter($class)
    {
        $this->classCounter[$class] += 1;
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
            'only_once' => $onlyOnce,
        ];

        $this->makeLog('Override set for: ' . $namespace . ', to: ' . $overrider . '. Once: ' . (string)$onlyOnce);

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
