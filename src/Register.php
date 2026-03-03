<?php

declare(strict_types=1);

namespace BlueRegister;

use BlueRegister\Events\Event;
use BlueEvent\Event\Base\EventDispatcher;
use SimpleLog\Log as SimpleLog;

class Register
{
    /**
     * @var array
     */
    protected array $config = [
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
    protected array $registeredObjects = [];

    /**
     * store list of objects called as singletons
     *
     * @var array
     */
    protected array $singletons = [];

    /**
     * store list of overrides
     *
     * @var array
     */
    protected array $overrides = [];

    /**
     * store information about number of created objects
     *
     * @var array
     */
    protected array $classCounter = [];

    /**
     * @var bool
     */
    protected bool $allowOverride = false;

    /**
     * @var null|Event
     */
    protected ?Event $event = null;

    /**
     * @var null|Log
     */
    protected ?Log $log = null;

    /**
     * Register constructor. Allow to set config on create
     *
     * @param array $config
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);

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
     * @throws RegisterException
     */
    public function factory(string $namespace, array $args = []): mixed
    {
        $namespace = $this->checkOverrider($namespace);

        $this->classExists($namespace)
            ->callEvent('register_before_create', [$namespace, $args]);

        try {
            $object = new $namespace(...$args);
        } catch (\Exception $exception) {
            throw new RegisterException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->callEvent('register_after_create', [$object]);

        $this->setClassCounter($namespace);
        $this->registeredObjects[$namespace] = \get_class($object);

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
     * @param string|null $name
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws RegisterException
     */
    public function singletonFactory(string $namespace, array $args = [], ?string $name = null): mixed
    {
        if (\is_null($name)) {
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
    protected function checkOverrider(string $namespace): string
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
     * @param string|null $key
     * @return mixed
     */
    public function getConfig(?string $key = null): mixed
    {
        if (\is_null($key)) {
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
    public function setConfig(string $key, mixed $val): self
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
    protected function classExists(string $namespace): self
    {
        if (!\class_exists($namespace)) {
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
    protected function callEvent(string $name, array $data): self
    {
        $this->event?->callEvent($name, $data);

        return $this;
    }

    /**
     * @param array|string $message
     * @return $this
     */
    protected function makeLog(array|string $message): self
    {
        $this->log?->makeLog($message);

        return $this;
    }

    /**
     * destroy called object
     *
     * @param string|null $name
     * @return $this
     */
    public function destroySingleton(?string $name = null): self
    {
        if (\is_null($name)) {
            $this->singletons = [];
        }

        if (isset($this->singletons[$name])) {
            unset($this->singletons[$name]);
        }

        $this->makeLog('Destroy singleton: ' . (\is_null($name) ? 'all' : $name));

        return $this;
    }

    /**
     * get called object
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws RegisterException
     */
    public function getSingleton(string $name): mixed
    {
        return $this->singletonFactory($name);
    }

    /**
     * return all registered objects by their references
     *
     * @return array
     */
    public function getRegisteredObjects(): array
    {
        return $this->registeredObjects;
    }

    /**
     * return list of created by Loader::getClass objects and number of executions
     *
     * @return array
     */
    public function getClassCounter(): array
    {
        return $this->classCounter;
    }

    /**
     * increment by 1 class execution
     *
     * @param string $class
     * @return Register
     */
    protected function setClassCounter(string $class): self
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
    public function setOverrider(string $namespace, string $overrider, bool $onlyOnce = false): self
    {
        $this->overrides[$namespace] = [
            'overrider' => $overrider,
            'only_once' => $onlyOnce,
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
     * @param string|null $namespace
     * @return $this
     */
    public function unsetOverrider(?string $namespace = null): self
    {
        if (\is_null($namespace)) {
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
    public function enableOverride(): self
    {
        $this->allowOverride = true;
        return $this;
    }

    /**
     * disable overriding
     *
     * @return $this
     */
    public function disableOverride(): self
    {
        $this->allowOverride = false;
        return $this;
    }

    /**
     * return information that overriding is enabled
     *
     * @return bool
     */
    public function isOverrideEnable(): bool
    {
        return $this->allowOverride;
    }
}
