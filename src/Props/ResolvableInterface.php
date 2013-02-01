<?php

namespace Props;

/**
 * An object that implements this interface can be resolved to a value at a later time. Since the
 * container is passed in, the object can pull other values from the container to resolve the
 * value.
 *
 * @author Steve Clay <steve@mrclay.org>
 */
interface ResolvableInterface
{

    /**
     * @abstract
     * @param Container $container
     * @return mixed
     */
    public function resolveValue(Container $container);
}
