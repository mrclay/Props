<?php

namespace Props;

/**
 * Object that builds an object instance to return it as a value.
 *
 * Resolvable objects such as Reference can be used to determine the classname,
 * constructor arguments, or values to be passed to setter methods.
 *
 * <code>
 * // This factory, when resolved, will create a Pizza object, passing in two arguments,
 * // the second of which is pulled from the container. Before the pizza is returned,
 * // the method "setDough" is called and passed the container's value for "dough".
 * $factory = new Factory('Pizza', array('deluxe', $di->ref('cheese')));
 * $factory->addMethodCall('setDough', $di->ref('dough'));
 * $di->pizza = $factory;
 * </code>
 *
 * @see Container::setFactory()
 *
 * @author Steve Clay <steve@mrclay.org>
 */
class Factory implements ResolvableInterface
{

    protected $class;
    protected $arguments;
    protected $container;
    protected $plan = array();

    /**
     * @param string|ResolvableInterface $class
     * @param array $constructorArguments
     */
    public function __construct($class, array $constructorArguments = array())
    {
        $this->class = $class;
        $this->arguments = array_values($constructorArguments);
    }

    /**
     * Set an argument for the constructor
     *
     * @param int $index
     * @param mixed $value
     * @return Factory
     * @throws \InvalidArgumentException
     */
    public function setConstructorArgument($index, $value)
    {
        if (((int)$index != $index) || $index < 0) {
            throw new \InvalidArgumentException('index must be a non-negative integer');
        }
        if ($index >= count($this->arguments)) {
            $this->arguments = array_pad($this->arguments, $index + 1, null);
        }
        $this->arguments[$index] = $value;
        return $this;
    }

    /**
     * Prepare a setter method to be called on the constructed object before being returned. A reference
     * object can be used to have the value pulled from the container at read-time.
     *
     * @param string $method
     * @param mixed $value a value or a value which can be resolved at read-time
     * @return Factory
     */
    public function addMethodCall($method, $value)
    {
        $this->plan[] = array('setter', $method, $value);
        return $this;
    }

    /**
     * Prepare a property to be set on the constructed object before being returned. A reference
     * object can be used to have the value pulled from the container at read-time.
     *
     * @param string $property
     * @param mixed $value a value or a value which can be resolved at read-time
     * @return Factory
     */
    public function addPropertySet($property, $value)
    {
        $this->plan[] = array('prop', $property, $value);
        return $this;
    }

    /**
     * @param Container $container
     * @return object
     * @throws ValueUnresolvableException
     */
    public function resolveValue(Container $container)
    {
        $this->container = $container;

        $class = $this->_resolve($this->class);
        if (!is_string($class)) {
            throw new ValueUnresolvableException('Needed a class name, but a non-string was resolved');
        }
        $class = ltrim($class, '\\');
        if (!class_exists($class)) {
            throw new ValueUnresolvableException("The class $class was not defined and failed to autoload");
        }

        if (empty($this->arguments)) {
            $obj = new $class();
        } else {
            $arguments = array_values($this->arguments);
            $arguments = array_map(array($this, '_resolve'), $arguments);
            $ref = new \ReflectionClass($class);
            $obj = $ref->newInstanceArgs($arguments);
        }

        foreach ($this->plan as $step) {
            list($type, $name, $value) = $step;
            if ($type === 'setter') {
                $obj->{$name}($this->_resolve($value));
            } else {
                $obj->{$name} = $this->_resolve($value);
            }
        }

        // don't want to keep a reference to the container
        $this->container = null;

        return $obj;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function _resolve($value)
    {
        if ($value instanceof ResolvableInterface) {
            /* @var ResolvableInterface $value */
            $value = $value->resolveValue($this->container);
        }
        return $value;
    }
}
