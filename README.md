# Props [![Build Status](https://travis-ci.org/mrclay/Props.png)](https://travis-ci.org/mrclay/Props)

Most [Dependency Injection](http://www.mrclay.org/2014/04/06/dependency-injection-ask-for-what-you-need/) containers have fetch operations like `$di->get('foo')` or `$di['foo']`, which doesn't allow your IDE to know the type of value received, nor offer you any help remembering/typing key names.

With **Props**, you subclass the container and provide `@property` PHPDoc declarations for the values that will be available at runtime. This gives you the benefits of static analysis/code completion via your IDE and other tools in a dynamic, lazy-loading environment.

An example will help:

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
        };

        $this->slice = function (MyDI $c) {
            return $c->pizza->getSlice();
        };
    }
}

$di = new MyDI;

// You can request dependencies in any order. They're resolved as needed.

$di->new_slice(); // This first resolves and caches the cheese, dough, and pizza.

$di->pizza; // Your IDE recognizes this as a Pizza object!
```

Essentially your IDE sees the container as a plain old class of typed properties, allowing it to offer suggestions of available properties, autocomplete their names, and autocomplete the objects returned. It gives you much more power when providing static analysis and automated refactoring.

## Features

You can specify dependencies via direct setting:

```php
$di->aaa = new AAA();
```

Or, more powerfully, by providing a [resolvable](https://github.com/mrclay/Props/blob/master/src/Props/ResolvableInterface.php#L5) object, like a Closure:

```php
$di->bbb = function (MyDI $c) {
    // the container will be passed in
    return new BBB($c->aaa);
};
```

Resolved dependencies are cached, returning the same instance:

```php
$di->bbb === $di->bbb; // true
```

If you don't want caching, use `new_PROPERTYNAME()` to fetch a fresh instance:

```php
$di->new_bbb() !== $di->new_bbb(); // false
```

You can create a [reference](https://github.com/mrclay/Props/blob/master/src/Props/Reference.php#L5) to another dependency, or even another DI container:

```php
// this will fetch ->aaa when the reference is resolved
$ref = $di->ref('aaa');

// used it as an alias
$di->ccc = $di->ref('aaa');

// referencing another container
$di2->ccc = $di1->ref('ccc', true);
```

Besides Closures, you can use an [Invoker](https://github.com/mrclay/Props/blob/master/src/Props/Invoker.php#L5) or [Factory](https://github.com/mrclay/Props/blob/master/src/Props/Factory.php#L5) to specify how to find/build dependencies, but really, anonymous functions are the most readable solution.

## Requirements

 * PHP 5.3

### License (MIT)

See [LICENSE](https://github.com/mrclay/Props/blob/master/src/LICENSE).
