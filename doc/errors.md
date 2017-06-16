# Errors
Their always is possibility that something goes wrong during creating object.
_BlueRegister_ throw only one exception when try to create object, other exceptions
are connected with event and log support.  
Standard Exception is throw only if given namespace was not found.

## Exception list

* **InvalidArgumentException** _Class don't exists: {full namespace}_
* **LogicException** _Event should be instance of \BlueEvent\Event\Base\Interfaces\EventDispatcherInterface: {class name}_
* **LogicException** _Cannot create Event instance: {class name}_
* **LogicException** _Log should be instance of SimpleLog\LogInterface: {class name}_
* **LogicException** _Cannot create Log instance: {class name}_
* **RuntimeException** _System killed by Register Exception. Unknown class:  {class name}_ - called by `RegisterException`

## Exception event
When event handling is enabled and namespace was not found, their will be aso triggered special
event called `register_class_dont_exists`, just before exception.  
For that event was created special object `RegisterException` and allow to throw a another
exception `RuntimeException` to handle by upper layer of application.  
More description in [Events](https://github.com/bluetree-service/event/doc/events.md) documentation
