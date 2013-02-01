<?php

namespace Props\Tests;

use Props\Container;
use Props\Reference;

class ReferenceTest extends \PHPUnit_Framework_TestCase {

    public function testUnboundReference() {
        $di1 = new Container;
        $di1->foo = 'Foo from di1';
        $di2 = new Container();
        $di2->foo = 'Foo from di2';

        $ref = new Reference('foo');
        $this->assertEquals('Foo from di1', $ref->resolveValue($di1));
        $this->assertEquals('Foo from di2', $ref->resolveValue($di2));
    }

    public function testBoundReference() {
        $di1 = new Container;
        $di1->foo = 'Foo from di1';
        $di2 = new Container();
        $di2->foo = 'Foo from di2';

        $ref = new Reference('foo', $di1);
        $this->assertEquals('Foo from di1', $ref->resolveValue($di2));
    }

    public function testUnboundNewReference() {
        $di = new Container;
        $di->bar = function () { return new \stdClass(); };

        $ref = new Reference('new_bar()');
        $bar1 = $ref->resolveValue($di);
        $bar2 = $ref->resolveValue($di);
        $this->assertInstanceOf('stdClass', $bar1);
        $this->assertInstanceOf('stdClass', $bar2);
        $this->assertNotSame($bar1, $bar2);
    }

    public function testBoundNewReference() {
        $di = new Container();
        $di->bar = function () {
            return (object) array(
                'bar' => 'Bar',
            );
        };

        $ref = new Reference('new_bar()', $di);
        $bar = $ref->resolveValue(new Container());
        $this->assertEquals('Bar', $bar->bar);
    }
}