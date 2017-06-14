# Usage
Register allow to create object instances in some more advanced way. Allow to override object to create and create and
keep some created objects to use them as singletons.

## Simple usage
The simplest way is used to simply create and return instance of some given class using factory method.

```php
$object = (new Register)->factory('namespace', ['optional parameters']);
```

## Configuration
Register have some additional configuration that allow to set event and log mechanism. Both of them are described in
separate documentation.  
Basic set configuration is allowed by giving configuration array in Register constructor. Bellow is example with default
stored by Register configuration.

```php
$register = new Register([
    'log' => false,
    'events' => false,
    'log_object' => 'SimpleLog\Log',
    'event_object' => 'BlueEvent\Event\Base\EventDispatcher',
    'event_config' => [],
]);
```

### Configuration options

* **log** - Enable or disable log mechanism (`true` | `false`)
* **events** - Enable or disable event mechanism (`true` | `false`)
* **log_object** - Set class name or existing object to handle log mechanism
* **event_object** - Set class name or existing object to handle event mechanism
* **event_config** - Event object and listeners configuration for event mechanism

### Configuration methods
Register allows also set up configuration by usage special method `setConfig` after creating instance. That method take
two parameters, first is name of configuration key, second is configuration parameter

## Factory Overriding
Register allows to create object by factory from some other class than is define using override mechanism.  
If override is on and we have set up namespace for override, factory will create object from override class, not defined
in factory method.

```php
$register = new Register;
$instance = $register
    ->enableOverride()
    ->setOverrider('NamespaceToBeOverriden', 'OverridenNamespace')
    ->factory('NamespaceToBeOverriden');
```

Using that code `$instance` will have instance of `OverridenNamespace`. Each time that `factory` will be called for
`NamespaceToBeOverriden`.  
There is also possibility to run override just one time. For that we must set third argument for `setOverrider` on true.

```php
$register->setOverrider('NamespaceToBeOverriden', 'OverridenNamespace', true);
```

After that only first execution of `factory` will return `OverridenNamespace` instance.

## Singletons
Register allows to create Singletons in more elastic way that standard singleton objects. All instances are stored
inside register instance. Register to create singleton use standard `factory` method, so singleton before create can be
override.  
Singletons can be stored using full namespace, or specific name that is optional.  
To create or get singleton we have two methods. First is `singletonFactory` that allow you otionally to set constructor
parameters and specified name for singleton. Second is `getSingleton` that allow to create singleton only if you use
namespace, otherwise its return existing singleton.  
If you try to give singleton name and instance don't exist, Register will throw exception about not existing class.

```php
$register = new Register;
$instance1 = $register->getSingleton('fullNamespace');
$instance2 = $register->getSingleton('fullNamespace');
$instance3 = $register->getSingleton('fullNamespace');
```

```php
$register = new Register;
$instance1 = $register->singletonFactory('fullNamespace', $parameters, 'testSingleton');
$instance2 = $register->singletonFactory('testSingleton');
$instance3 = $register->getSingleton('testSingleton');
```

All instances will store the same object.

## All public methods
* **factory** - create object instance (namespace, optional parameters)
* **singletonFactory** - create or return singleton instance (namespace or name, optional parameters, optional name)
* **getConfig** - return whole config, or only for specified key
* **setConfig** - set config for specified key (key name, value)
* **destroySingleton** - destroy singleton or all singletons if name was not specified (name or namespace)
* **getSingleton** - return singleton instance for given name or namespace
* **getRegisteredObjects** - return all created class names
* **getClassCounter** - return count of calling given classes
* **setOverrider** - allow to set override class (original class namespace, overrider namespace, optional only once overriding)
* **unsetOverrider** - destroy overriding for given namespace, or for all
* **enableOverride** - turn on overriding
* **disableOverride** - turn off overriding
* **isOverrideEnable** - return bool info that overriding is enabled
