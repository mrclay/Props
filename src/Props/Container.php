<?php

namespace Props;

/**
 * Container holding values which can be resolved upon reading and optionally stored and shared
 * across reads.
 *
 * Values are read/set as properties.
 *
 * @note see scripts/example.php
 */
class Container {

	/**
	 * @var ResolvableInterface[]
	 */
	protected $_resolvables = array();

	protected $_cache = array();

	/**
	 * Fetch a value.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws MissingValueException
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->_cache)) {
			return $this->_cache[$name];
		}
		if (!isset($this->_resolvables[$name])) {
			throw new MissingValueException("Missing value: $name");
		}
		$value = $this->_resolvables[$name]->resolveValue($this);
		$this->_cache[$name] = $value;
		return $value;
	}

	/**
	 * Set a value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 */
	public function __set($name, $value) {
		if ($name[0] === '_') {
			throw new \InvalidArgumentException('Name cannot begin with underscore');
		}
		unset($this->_cache[$name]);
		unset($this->_resolvables[$name]);

        if ($value instanceof \Closure) {
            $value = new Invoker($value);
        }

		if ($value instanceof ResolvableInterface) {
			$this->_resolvables[$name] = $value;
		} else {
			$this->_cache[$name] = $value;
		}
	}

	/**
	 * @param string $name
	 */
	public function __unset($name) {
		unset($this->_cache[$name]);
		unset($this->_resolvables[$name]);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		return isset($this->_resolvables[$name]) || array_key_exists($name, $this->_cache);
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
	public function __call($method, $args) {
		if (0 !== strpos($method, 'new_')) {
			throw new \BadMethodCallException("Method name must begin with 'new_'");
		}
		$name = substr($method, 4);
		if (!isset($this->_resolvables[$name])) {
			throw new ValueUnresolvableException("Unresolvable value: $name");
		}
		return $this->_resolvables[$name]->resolveValue($this);
	}

	/**
	 * Can we fetch a new value via new_$name()?
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isResolvable($name) {
		return isset($this->_resolvables[$name]);
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
	public function ref($name, $bound = false) {
		$cont = $bound ? $this : null;
		return new Reference($name, $cont);
	}

    /**
     * Helper to attach and return a factory instance
     *
     * @param string $name
     * @param string $class
     * @param array $constructorArgs
     * @return Factory
     */
    public function setFactory($name, $class, array $constructorArgs = array()) {
        $fact = new Factory($class, $constructorArgs);
        $this->{$name} = $fact;
        return $fact;
    }
}
