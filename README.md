# BlueRegister

[![Latest Stable Version](https://poser.pugx.org/bluetree-service/register/v/stable.svg)](https://packagist.org/packages/bluetree-service/register)
[![Total Downloads](https://poser.pugx.org/bluetree-service/register/downloads.svg)](https://packagist.org/packages/bluetree-service/register)
[![License](https://poser.pugx.org/bluetree-service/register/license.svg)](https://packagist.org/packages/bluetree-service/register)
[![Dependency Status](https://www.versioneye.com/user/projects/594527b26725bd00163ecc5a/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/594527b26725bd00163ecc5a)
[![Documentation Status](https://readthedocs.org/projects/bluetree-serviceregister/badge/?version=latest)](http://bluetree-serviceregister.readthedocs.io/en/latest/?badge=latest)

##### Builds
| Travis | Scrutinizer |
|:---:|:---:|
| [![Build Status](https://travis-ci.org/bluetree-service/register.svg)](https://travis-ci.org/bluetree-service/register) | [![Build Status](https://scrutinizer-ci.com/g/bluetree-service/register/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/register/build-status/master) |

##### Coverage
| Coveralls | Scrutinizer |
|:---:|:---:|
| [![Coverage Status](https://coveralls.io/repos/github/bluetree-service/register/badge.svg?branch=master)](https://coveralls.io/github/bluetree-service/register?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/bluetree-service/register/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/register/?branch=master) |

##### Quality
| Code Climate | Scrutinizer | Sensio Labs |
|:---:|:---:|:---:|
| [![Code Climate](https://codeclimate.com/github/bluetree-service/register/badges/gpa.svg)](https://codeclimate.com/github/bluetree-service/register) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bluetree-service/register/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/register/?branch=master) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/06b4a644-7432-444c-ae2f-1fe61bd77831/mini.png)](https://insight.sensiolabs.com/projects/06b4a644-7432-444c-ae2f-1fe61bd77831) |
|  | [![Code Intelligence Status](https://scrutinizer-ci.com/g/bluetree-service/register/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence) |  |

Register allow to create and load objects in some advanced way.

### Included classes
* **BlueRegister\Events\RegisterEvent** - Basic event object for Register Events
* **BlueRegister\Events\RegisterException** - Basic event for Register Exception events (allow to kill system by special exception)
* **BlueRegister\Events\Event** - Register event system handling
* **BlueRegister\Register** - Main library class, allow to create object instances and singletons
* **BlueRegister\RegisterException** - Throw only when unable to create new object
* **BlueRegister\Log** - Register log system handling

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

* PHP 5.6 or higher


## Change log
All release version changes:  
[Change log](https://github.com/bluetree-service/register/doc/changelog.md "Change log")

## License
This bundle is released under the Apache license.  
[Apache license](https://github.com/bluetree-service/register/LICENSE "Apache license")

## Travis Information
[Travis CI Build Info](https://travis-ci.org/bluetree-service/register)
