# Event Log
To have better control about creating objects we can use log system implemented
into Register.  
All logs by default are stored in specified log file given in configuration.  
To do that you can use `log` config value set on `true`.

Event is using by default `bluetree-service/simple-log` library in the newest
available version.  
But you can implement some other libraries, which must be consistent with
`\SimpleLog\LogInterface`.

## Log messages


## Log message example
Default log has specified format, that contains such information as:

1. Basic log message
2. Date ant time of execution
3. For factory also (singletonFactory) all parameters for creating object

### Log message example (for `SimpleLog`)

```
28-06-2016 - 09:39:09
- Destroy singleton: all
-----------------------------------------------------------
```

## Extending Log class
Format of log message can be changed by creating own Log class and inject
new instance to event dispatcher. New class should implements `LogInterface` and
have one public method `makeLog` that get array of parameters to log event.

Remember that log file is stored in `SimpleLog` default path. To change it
set up own simple log instance and give it by configuration parameter `log_object`.  
More details in [usage](https://github.com/bluetree-service/register/doc/usage.md) documentation.
