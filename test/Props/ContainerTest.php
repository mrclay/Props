<?php

namespace Props\Tests;

use Props\Container;
use Props\Factory;

class ContainerTest extends \PHPUnit_Framework_TestCase {

	const TEST_CLASS = 'Props\Tests\ContainerTestObject';

	protected function getTestContainer(array $props = array()) {
		$di = new Container();
		foreach ($props as $name => $val) {
			$di->{$name} = $val;
		}
		return $di;
	}

	public function getFoo(Container $di) {
		return $di->foo;
	}

	public function testContainerEmpty() {
		$di = new Container();
		$this->assertFalse(isset($di->foo));
	}

	public function testContainerSetNonResolvable() {
		$di = new Container();

		$di->foo = 'Foo';
		$this->assertTrue(isset($di->foo));
		$this->assertFalse($di->isResolvable('foo'));
	}

	public function testContainerSetResolvable() {
		$di = new Container();

		$di->foo = new Factory(self::TEST_CLASS);
		$this->assertTrue(isset($di->foo));
		$this->assertTrue($di->isResolvable('foo'));
	}

	public function testContainerGetMissingValue() {
		$di = new Container();
		$this->setExpectedException('Props\MissingValueException');
		$di->foo;
	}

	public function testContainerGetNewUnresolvableValue() {
		$di = new Container();
		$di->foo = 'Foo';

		$this->setExpectedException('Props\ValueUnresolvableException');
		$di->new_foo();
	}

	public function testContainerSetAfterRead() {
		$di = new Container();

		$di->foo = 'Foo';
		$di->foo = 'Foo2';
		$this->assertEquals('Foo2', $di->foo);
	}

	public function testContainerHandlesNullValue() {
		$di = new Container();

		$di->null = null;
		$this->assertTrue(isset($di->null));
		$this->assertNull($di->null);
	}

	public function testContainerGetResolvables() {
		$di = new Container();

		$di->foo = new Factory(self::TEST_CLASS);
		$foo1 = $di->foo;
		$foo2 = $di->foo;
		$this->assertInstanceOf(self::TEST_CLASS, $foo1);
		$this->assertSame($foo1, $foo2);

		$foo3 = $di->new_foo();
		$foo4 = $di->new_foo();
		$this->assertInstanceOf(self::TEST_CLASS, $foo3);
		$this->assertInstanceOf(self::TEST_CLASS, $foo4);
		$this->assertNotSame($foo3, $foo4);
		$this->assertNotSame($foo1, $foo3);
	}

	public function testContainerKeyNamespace() {
		$di = new Container();
		$di->foo = new Factory(self::TEST_CLASS);
		$di->new_foo = 'Foo';

		$this->assertInstanceOf(self::TEST_CLASS, $di->new_foo());
		$this->assertEquals('Foo', $di->new_foo);
	}

	public function testContainerRemove() {
		$di = new Container();
		$di->foo = 'Foo';

		unset($di->foo);
		$this->assertFalse(isset($di->foo));
	}

	public function testContainerAccessRemovedValue() {
		$di = new Container();
		$di->foo = 'Foo';
		unset($di->foo);

		$this->setExpectedException('Props\MissingValueException');
		$di->foo;
	}

	public function testContainerRef() {
		$di1 = new Container();
		$di1->foo = 'Foo1';

		$di2 = new Container();
		$di2->foo = 'Foo2';

		$unboundFooRef = $di1->ref('foo');
		$boundFooRef = $di1->ref('foo', true);

		$this->assertInstanceOf('Props\Reference', $unboundFooRef);
		$this->assertInstanceOf('Props\Reference', $boundFooRef);

		$this->assertEquals('Foo2', $unboundFooRef->resolveValue($di2));
		$this->assertEquals('Foo1', $boundFooRef->resolveValue($di2));
	}

    public function testContainerSetFactory() {
        $di = new Container();

        $fact = $di->setFactory('foo', self::TEST_CLASS)->addPropertySet('bar', 'bar');

        $this->assertInstanceOf('Props\Factory', $fact);

        $foo = $di->foo;

        $this->assertInstanceOf(self::TEST_CLASS, $foo);
        $this->assertEquals('bar', $foo->bar);
    }
}

class ContainerTestObject {
	public $calls;
	public $args;
	public function __construct() { $this->args = func_get_args(); }
	public function __call($name, $args) { $this->calls[$name] = $args[0]; }
}
