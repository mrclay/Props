<?php

namespace Props\Tests;

use Props\Factory;
use Props\Container;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    const TEST_CLASS = 'Props\Tests\FactoryTestObject';

    protected function getTestContainer(array $props = array())
    {
        $di = new Container();
        foreach ($props as $name => $val) {
            $di->{$name} = $val;
        }
        return $di;
    }

    public function testFactoryClassAndArguments()
    {
        $di = $this->getTestContainer();

        $fact = new Factory('\\' . self::TEST_CLASS);
        $obj = $fact->resolveValue($di);
        $this->assertInstanceOf(self::TEST_CLASS, $obj);
        $this->assertEmpty($obj->args);

        $fact = new Factory(self::TEST_CLASS, array('one', 2));
        $obj = $fact->resolveValue($di);
        $this->assertEquals(array('one', 2), $obj->args);
    }

    public function testFactoryCallsSetters()
    {
        $di = $this->getTestContainer(array('bar' => 'Bar'));

        $fact = new Factory(self::TEST_CLASS);
        $fact->addMethodCall('setArray', array(1, 2, 3));
        $obj = $fact->resolveValue($di);
        $this->assertEquals(array('setArray' => array(1, 2, 3)), $obj->calls);
    }

    public function testFactoryCanUseResolvedValues()
    {
        $di = $this->getTestContainer(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'testObjClass' => self::TEST_CLASS,
            'anArray' => array(1, 2, 3),
        ));

        $fact = new Factory($di->ref('testObjClass'));
        $obj = $fact->resolveValue($di);
        $this->assertInstanceOf(self::TEST_CLASS, $obj);

        $fact = new Factory(self::TEST_CLASS, array($di->ref('foo')));
        $obj = $fact->resolveValue($di);
        $this->assertEquals(array('Foo'), $obj->args);

        $fact = new Factory(self::TEST_CLASS);
        $fact->addMethodCall('setBar', $di->ref('bar'));
        $obj = $fact->resolveValue($di);
        $this->assertEquals(array('setBar' => 'Bar'), $obj->calls);
    }

    public function testFactoryRequiresClassNameToResolveToString()
    {
        $di = $this->getTestContainer(array('anArray' => array(1, 2, 3)));
        $fact = new Factory($di->ref('anArray'));

        $this->setExpectedException('Props\ValueUnresolvableException');
        $fact->resolveValue($di);
    }
}

class FactoryTestObject
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
