# Props [![Build Status](https://travis-ci.org/mrclay/Props.png)](https://travis-ci.org/mrclay/Props)

Most [Dependency Injection](http://www.mrclay.org/2014/04/06/dependency-injection-ask-for-what-you-need/) containers have fetch operations, like `$di->get('foo')` or `$di['foo']`, which don't allow your IDE to know the type of value received, nor offer you any help remembering/typing key names.

With **Props**, you access values via a property read: `$di->foo`. No big deal until you subclass the container and provide `@property` PHPDoc declarations for the values that will be available at runtime. Then you get the benefits of static analysis/code completion via your IDE and other tools in a dynamic, lazy-loading environment.

An example will help:

```php
/**
 * @property-read Foo $foo
 */
class MyDI extends \Props\Container {
    public function __construct() {
        $this->foo = new Foo();
    }
}

$di = new MyDI();
$di->foo; // your IDE knows this is a Foo instance
```

In a more complex example, we show how anonymous functions are used as factories:

```php
/**
 * @property-read string $style
 * @property-read Dough  $dough
 * @property-read Cheese $cheese
 * @property-read Pizza  $pizza
 * @method        Slice  new_slice()
 */
class MyDI extends \Props\Container {
    public function __construct() {
        $this->style = 'deluxe';

        $this->dough = function (MyDI $c) {
            return new Dough();
        };

        $this->cheese = function (MyDI $c) {
            return CheeseFactory::getCheese();
        };

        $this->pizza = function (MyDI $c) {
            $pizza = new Pizza($c->style, $c->cheese);
            $pizza->setDough($c->dough);
            return $pizza;
        };

        $this->slice = function (MyDI $c) {
            return $c->pizza->getSlice();
        };
    }
}

$di = new MyDI;

$di->pizza; // This first resolves and caches the cheese and dough.

$di->pizza; // The same pizza instance as above
```

Since "slice" has a factory function set, we can call `new_slice()` to get fresh instances from it:

```php
$di->new_slice(); // a new Slice instance
$di->new_slice(); // a new Slice instance
```

Your IDE sees the container as a plain old class of typed properties, allowing it to offer suggestions of available properties, autocomplete their names, and autocomplete the objects returned. It gives you much more power when providing static analysis and automated refactoring.

## Compatibility

`Props\Container` implements [`ContainerInterface`](https://github.com/container-interop/container-interop).

## Overview

You can specify dependencies via direct setting:

```php
$di->aaa = new AAA();
```

You can specify factories by setting a `Closure`, or by using the `setFactory()` method. These are equivalent:

```php
$di->bbb = function (MyDI $c) {
    // the container will be passed in
    return new BBB($c->aaa);
};

$di->setFactory('bbb', function (MyDI $c) {
    return new BBB($c->aaa);
});
```

Resolved dependencies are cached, returning the same instance:

```php
$di->bbb === $di->bbb; // true
```

If you don't want caching, use `new_PROPERTYNAME()` to fetch a fresh instance:

```php
$di->new_bbb() === $di->new_bbb(); // false
```

Regular value sets do not store a factory, so you may want to check `hasFactory()` before you use `new_PROPERTYNAME()`:

```php
// store a value
$di->ccc = new CCC();
$di->hasFactory('ccc'); // false

// store a factory
$di->ccc = function () {
    return new CCC();
};
$di->hasFactory('ccc'); // true
```

## Pimple port

You can probably tell Props is influenced by [Pimple](http://pimple.sensiolabs.org/). If you're used to its API you can switch easily to `Props\Pimple`, which just uses property set/gets instead of ArrayAccess (it passes a port of Pimple's test suite as well). This lets you subclass and add `@property` declarations just like `Props\Container`.

You can see an [example](https://github.com/mrclay/Props/blob/master/scripts/example-pimple.php) that's similar to the Pimple docs.

## Requirements

 * PHP 5.3

### License (MIT)

See [LICENSE](https://github.com/mrclay/Props/blob/master/src/LICENSE).
