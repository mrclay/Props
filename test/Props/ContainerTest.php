<?php

namespace Props;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'Props\ContainerTestObject';

    public function testBasicInterop()
    {
        $di = new Container();
        $this->assertInstanceOf('Interop\Container\ContainerInterface', $di);

        $this->assertFalse($di->has('foo'));
        $di->foo = 'bar';
        $this->assertTrue($di->has('foo'));
    }

    /**
     * @expectedException \Interop\Container\Exception\NotFoundException
     */
    public function testInteropNotFound()
    {
        $di = new Container();
        $di->get('foo');
    }

    /**
     * @expectedException \Interop\Container\Exception\ContainerException
     */
    public function testInteropException1()
    {
        $di = new Container();
        $di->setFactory('foo', null);
    }

    /**
     * @expectedException \Interop\Container\Exception\ContainerException
     */
    public function testInteropException2()
    {
        $di = new Container();
        $di->setFactory('foo', function () {
            throw new \Exception();
        });
        $di->foo;
    }

    public function testEmpty()
    {
        $di = new Container();
        $this->assertFalse(isset($di->foo));
        $this->assertFalse($di->has('foo'));
    }

    public function testValueSetRemovesFactory()
    {
        $di = new Container();
        $di->foo = function () {
            return 'Bar';
        };
        $di->foo = 'Foo';
        $this->assertTrue(isset($di->foo));
        $this->assertFalse($di->hasFactory('foo'));
    }

    public function testSetResolvable()
    {
        $di = new Container();
        $di->foo = function () {
            return new ContainerTestObject();
        };

        $this->assertTrue(isset($di->foo));
        $this->assertTrue($di->has('foo'));
        $this->assertTrue($di->hasFactory('foo'));
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testReadMissingValue()
    {
        $di = new Container();
        $di->foo;
    }

    /**
     * @expectedException \Props\NotFoundException
     */
    public function testGetMissingValue()
    {
        $di = new Container();
        $di->get('foo');
    }

    public function testGetNewUnresolvableValue()
    {
        $di = new Container();
        $di->foo = 'Foo';

        $this->setExpectedException('Props\NotFoundException');
        $di->new_foo();
    }

    public function testSetAfterRead()
    {
        $di = new Container();

        $di->foo = 'Foo';
        $di->foo = 'Foo2';
        $this->assertEquals('Foo2', $di->foo);
    }

    public function testHandlesNullValue()
    {
        $di = new Container();
        $di->null = null;
        $this->assertTrue(isset($di->null));
        $this->assertTrue($di->has('null'));
        $this->assertNull($di->null);
        $this->assertNull($di->get('null'));
    }

    public function testFactoryReceivesContainer()
    {
        $di = new Container();
        $di->foo = function () {
            return func_get_args();
        };
        $foo = $di->foo;
        $this->assertSame($foo[0], $di);
        $this->assertEquals(count($foo), 1);
    }

    public function testGetResolvables()
    {
        $di = new Container();

        $di->foo = function () {
            return new ContainerTestObject();
        };
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

    public function testKeyNamespace()
    {
        $di = new Container();
        $di->foo = function () {
            return new ContainerTestObject();
        };
        $di->new_foo = 'Foo';

        $this->assertInstanceOf(self::TEST_CLASS, $di->new_foo());
        $this->assertEquals('Foo', $di->new_foo);
    }

    public function testUnset()
    {
        $di = new Container();
        $di->foo = 'Foo';

        unset($di->foo);
        $this->assertFalse(isset($di->foo));
    }

    public function testAccessUnsetValue()
    {
        $di = new Container();
        $di->foo = 'Foo';
        unset($di->foo);

        $this->setExpectedException('Props\NotFoundException');
        $di->foo;
    }

    public function testSetFactory()
    {
        $di = new Container();
        $di->setFactory('foo', function () {
            $obj = new ContainerTestObject();
            $obj->bar = 'bar';
            return $obj;
        });

        $foo = $di->foo;

        $this->assertInstanceOf(self::TEST_CLASS, $foo);
        $this->assertEquals('bar', $foo->bar);
    }

    public function testSetValue()
    {
        $di = new Container();
        $di->setValue('foo', function () {});

        $this->assertInstanceOf('Closure', $di->foo);
    }
}

class ContainerTestObject
{
    public $calls;
    public $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }

    public function __call($name, $args)
    {
        $this->calls[$name] = $args[0];
    }
}
