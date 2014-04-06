# Props [![Build Status](https://travis-ci.org/mrclay/Props.png)](https://travis-ci.org/mrclay/Props)

**Props** is a simple [Dependency Injection](http://www.mrclay.org/2014/04/06/dependency-injection-ask-for-what-you-need/) container that allows retrieving values via custom property and method names. This gives you the benefits of static analysis/code completion via your IDE and other tools in a dynamic, lazy-loading environment.

Most DI containers have fetch operations like `$di->get('foo')` or `$di['foo']`, which doesn't allow your IDE to know the type of value received, nor offer you any help remembering/typing key names.

With Props, you subclass the container and provide `@property` PHPDoc declarations for the values that will be available at runtime. This makes the IDE see the container as a plain old class of typed properties, allowing it to offer suggestions of available properties, autocomplete their names, and autocomplete the objects returned. This also gives the IDE much more power when providing static analysis and automated refactoring.

```php
<?php

/**
 * @property-read AAA $aaa
 */
class MyDI extends \Props\Container {
    public function __construct() {
        $this->setFactory('aaa', 'AAA');
    }
}

$di = new MyDI;
$di->aaa; // the IDE recognizes this as an AAA object
```

See [scripts/example.php](https://github.com/mrclay/Props/blob/master/scripts/example.php#L15) for more usage.

## Additional Features

 * Property reads are cached, returning the same instance.
 * If `$di->foo` has a [resolvable](https://github.com/mrclay/Props/blob/master/src/Props/ResolvableInterface.php#L5) object (e.g. Factory, Invoker), then `$di->new_foo()` can be used to resolve a new value.
 * `$di->ref('foo')` returns a [reference](https://github.com/mrclay/Props/blob/master/src/Props/Reference.php#L5) that will read `$di->foo` later, only when the value is needed.
 * `$di->ref('new_foo()')` works the same way: the reference will call `$di->new_foo()` later.
 * References can be used in place of arguments in most operations
 * `$di->setFactory('foo', 'Foo')` returns the [Factory](https://github.com/mrclay/Props/blob/master/src/Props/Factory.php#L5) object, which can be programmed to call methods/set properties after constructing the object
 * [Invoker](https://github.com/mrclay/Props/blob/master/src/Props/Invoker.php#L5) can call a callback to resolve a value, passing in the container.
 * Closures are auto-wrapped with Invoker

## Requirements

 * PHP 5.3

### License (MIT)

Copyright (c) 2013 [Stephen Clay](http://www.mrclay.org/)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
