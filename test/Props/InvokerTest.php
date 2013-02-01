<?php

namespace Props\Tests;

use Props\Container;
use Props\Invoker;

class InvokerTest extends \PHPUnit_Framework_TestCase {

    public function getFoo(Container $di) {
        return $di->foo;
    }

    public function testInvokerPassesContainerToCallable() {
        $di = new Container();
        $di->foo = 'A Foo!';
        $obj = new Invoker(array($this, 'getFoo'));

        $foo = $obj->resolveValue($di);
        $this->assertEquals('A Foo!', $foo);
    }

    public function testInvokerChecksCallableInConstructor() {
        $this->setExpectedException('InvalidArgumentException');
        new Invoker(false);
    }

    public function testInvokerChecksCallableAtResolveTime() {
        $obj = new Invoker('y7r8437843');
        $this->setExpectedException('Props\ValueUnresolvableException');
        $obj->resolveValue(new Container());
    }
}