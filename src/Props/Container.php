<?php

namespace Props;

/**
 * Container holding values which can be resolved upon reading and optionally stored and shared
 * across reads.
 *
 * Values are read/set as properties.
 *
 * @note see scripts/example.php
 *
 * @author Steve Clay <steve@mrclay.org>
 */
class Container
{

    /**
     * @var ResolvableInterface[]
     */
    private $resolvables = array();

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Fetch a value.
     *
     * @param string $name
     * @return mixed
     * @throws MissingValueException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->cache)) {
            return $this->cache[$name];
        }
        if (!isset($this->resolvables[$name])) {
            throw new MissingValueException("Missing value: $name");
        }
        $value = $this->resolvables[$name]->resolveValue($this);
        $this->cache[$name] = $value;
        return $value;
    }

    /**
     * Set a value.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if ($name[0] === '_') {
            throw new \InvalidArgumentException('Name cannot begin with underscore');
        }
        unset($this->cache[$name]);
        unset($this->resolvables[$name]);

        if ($value instanceof \Closure) {
            $value = new Invoker($value);
        }

        if ($value instanceof ResolvableInterface) {
            $this->resolvables[$name] = $value;
        } else {
            $this->cache[$name] = $value;
        }
    }

    /**
     * Set a value to be later returned as is. You only need to use this if you wish to store
     * a Closure or something that implements Props\ResolvableInterface.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function setValue($name, $value)
    {
        if ($name[0] === '_') {
            throw new \InvalidArgumentException('Name cannot begin with underscore');
        }
        unset($this->resolvables[$name]);
        $this->cache[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->cache[$name]);
        unset($this->resolvables[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->resolvables[$name]) || array_key_exists($name, $this->cache);
    }

    /**
     * Fetch a freshly-resolved value.
     *
     * @param string $method method name must start with "new_"
     * @param array $args
     * @return mixed
     * @throws ValueUnresolvableException
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (0 !== strpos($method, 'new_')) {
            throw new \BadMethodCallException("Method name must begin with 'new_'");
        }
        $name = substr($method, 4);
        if (!isset($this->resolvables[$name])) {
            throw new ValueUnresolvableException("Unresolvable value: $name");
        }
        return $this->resolvables[$name]->resolveValue($this);
    }

    /**
     * Can we fetch a new value via new_$name()?
     *
     * @param string $name
     * @return bool
     */
    public function isResolvable($name)
    {
        return isset($this->resolvables[$name]);
    }

    /**
     * Helper to get a reference to a value in a container.
     *
     * @param string $name
     * @param bool $bound if given as true, the reference will always fetch from this container
     * @return Reference
     *
     * @note This function creates unbound refs by default, so that, in the future, if references need to be
     *       serialized, they will not have refs to the container
     */
    public function ref($name, $bound = false)
    {
        $cont = $bound ? $this : null;
        return new Reference($name, $cont);
    }

    /**
     * Helper to attach and return a Props\Factory instance
     *
     * @param string $name
     * @param string|ResolvableInterface $class
     * @param array $constructorArgs
     * @return Factory
     */
    public function setFactory($name, $class, array $constructorArgs = array())
    {
        $fact = new Factory($class, $constructorArgs);
        $this->{$name} = $fact;
        return $fact;
    }
}
