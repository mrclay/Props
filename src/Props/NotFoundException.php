<?php

namespace Props;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements ContainerExceptionInterface, NotFoundExceptionInterface
{
}
