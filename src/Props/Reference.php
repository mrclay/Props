<?php

namespace Props;

/**
 * Object that resolves a value by fetching it from a container at read-time.
 *
 * <code>
 * // When this value is read, "foo" will be read from the container.
 * $di->aliasOfFoo = $di->ref('foo');
 *
 * // When this value is read, a freshly-resolved "foo" will be returned from the container.
 * $di->aSecondFoo = $di->ref('new_foo()');
 *
 * // References can be bound to a particular container. Below the reference is bound to $di1, so
 * // when its value is read, "foo" will be read from $di1, even though $di2 is passed to it.
 * $di2->fooFromDi1 = $di1->ref('foo', true);
 * </code>
 *
 * @see Container::ref() as shortcut for creating these
 *
 * @author Steve Clay <steve@mrclay.org>
 */
class Reference implements ResolvableInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * If set, this reference will always read from this container
     *
     * @var Container
     */
    protected $boundContainer;

    /**
     * @param string $name Either the name of a key in the container, or new_$name() where $name is the key
     *                     to a resolvable value.
     * @param Container $boundContainer If given, we will always fetch from it.
     */
    public function __construct($name, Container $boundContainer = null)
    {
        $this->name = $name;
        $this->boundContainer = $boundContainer;
    }

    /**
     * @param Container $container
     * @return mixed
     */
    public function resolveValue(Container $container)
    {
        if ($this->boundContainer) {
            $container = $this->boundContainer;
        }
        if (0 === strpos($this->name, 'new_') && substr($this->name, -2) === '()') {
            return $container->{substr($this->name, 0, -2)}();
        }
        return $container->{$this->name};
    }
}
