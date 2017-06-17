# BlueRegister

[![Build Status](https://travis-ci.org/bluetree-service/register.svg)](https://travis-ci.org/bluetree-service/register)
[![Latest Stable Version](https://poser.pugx.org/bluetree-service/register/v/stable.svg)](https://packagist.org/packages/bluetree-service/register)
[![Total Downloads](https://poser.pugx.org/bluetree-service/register/downloads.svg)](https://packagist.org/packages/bluetree-service/register)
[![License](https://poser.pugx.org/bluetree-service/register/license.svg)](https://packagist.org/packages/bluetree-service/register)
[![Coverage Status](https://coveralls.io/repos/github/bluetree-service/register/badge.svg?branch=master)](https://coveralls.io/github/bluetree-service/register?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/{id}/badge.svg?style=flat)](https://www.versioneye.com/user/projects/{id})
[![Documentation Status](https://readthedocs.org/projects/register/badge/?version=latest)](https://readthedocs.org/projects/register/?badge=latest)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/{id}/mini.png)](https://insight.sensiolabs.com/projects/{id})
[![Code Climate](https://codeclimate.com/github/bluetree-service/register/badges/gpa.svg)](https://codeclimate.com/github/bluetree-service/register)

Register allow to create and load objects in some advanced way.

### Included classes
* **BlueRegister\Events\RegisterEvent** - Basic event object for Register Events
* **BlueRegister\Events\RegisterException** - Basic event for Register Exception events (allow to kill system by special exception)
* **BlueRegister\Register** - Main library class, allow to create object instances and singletons

## Documentation

### Usage
[Usage](https://github.com/bluetree-service/event/doc/usage.md)

### Events Configuration
[Events](https://github.com/bluetree-service/event/doc/events.md)

### Register Log
[Log each some behaviours](https://github.com/bluetree-service/event/doc/register_log.md)

### Errors
[Event listeners errors](https://github.com/bluetree-service/event/doc/errors.md)

## Install via Composer
To use _BlueRegister_ you can just download package and place it in your code. But recommended
way to use _BlueRegister_ is install it via Composer. To include _BlueRegister_
libraries paste into `composer.json`:

```json
{
    "require": {
        "bluetree-service/register": "version_number"
    }
}
```

## Project description

### Used conventions

* **Namespaces** - each library use namespaces (base is _BlueRegister_)
* **PSR-4** - [PSR-4](http://www.php-fig.org/psr/psr-4/) coding standard
* **Composer** - [Composer](https://getcomposer.org/) usage to load/update libraries

### Requirements

* PHP 5.5 or higher


## Change log
All release version changes:  
[Change log](https://github.com/bluetree-service/register/doc/changelog.md "Change log")

## License
This bundle is released under the Apache license.  
[Apache license](https://github.com/bluetree-service/register/LICENSE "Apache license")

## Travis Information
[Travis CI Build Info](https://travis-ci.org/bluetree-service/register)
