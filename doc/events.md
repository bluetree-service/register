# Events
Register can handle event system provided by [bluetree-service/event](https://github.com/bluetree-service/event)
or all other libraries that implements `EventDispatcherInterface`.  
Event system is very simple and handle only 4 events that can be triggered.  
To enable event system, see configuration in basic usage document.

## Register events:

### register_before_create
Triggered when try to create instance. Parameters:

1. **$namespace** - namespace of class to create object
2. **$args** - arguments for object

### register_after_create
Triggered just after create instance. Parameters:

1. **$object** - created object reference

### register_before_return_singleton
Triggered after create singleton instance and just before return. Parameters:

1. **$this->singletons[$name]** - singleton reference
2. **$args** - arguments for object
3. **$name** - singleton instance name (optional)

### register_class_dont_exists
Triggered when namespace was not found, just before exception. Parameters:

1. **$namespace** - namespace of class that was ont found

## Defined event objects
Register library have two predefined Event objects that should be used to handle all events.

### RegisterEvent Object
Basic Register event. Its only implement `BaseEvent` and don't provide any additional functionality.

### RegisterException Object
Should be used only for `register_class_dont_exists` event. That event implement some
functionality that allow to kill whole application by throwing `RuntimeException`.  
That functionality can be enabled or disabled, by default its disabled.  
Killing option is saved as value of static property of class, so once set on
it will be worked for all other triggers. All methods are also static.

* **allowKill** - allow to turn on or off killing after event trigger by boolean value (true - on, false - off)
* **isKillingAllowed** - return boolean information that killing is allowed
