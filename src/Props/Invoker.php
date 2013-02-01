<?php

namespace Props;

/**
 * Object that invokes a callable to resolve a value. The container is passed as an argument.
 *
 * <code>
 * $di->dough = new Invoker('Dough::factory');
 *
 * $di->cheese = new Invoker('get_cheese');
 *
 * $di->pizza = new Invoker(function ($di) {
 *     return new Pizza($di->dough, $di->cheese);
 * });
 * </code>
 *
 * @author Steve Clay <steve@mrclay.org>
 */
class Invoker implements ResolvableInterface
{

    protected $callable;

    /**
     * @param callable $callable
     * @throws \InvalidArgumentException
     */
    public function __construct($callable)
    {
        if (!is_callable($callable, true)) {
            throw new \InvalidArgumentException('$callable must be callable');
        }
        $this->callable = $callable;
    }

    /**
     * @param Container $container
     * @return mixed
     * @throws ValueUnresolvableException
     */
    public function resolveValue(Container $container)
    {
        if (!is_callable($this->callable)) {
            throw new ValueUnresolvableException('$callable looked callable, but was not');
        }
        return call_user_func($this->callable, $container);
    }
}
