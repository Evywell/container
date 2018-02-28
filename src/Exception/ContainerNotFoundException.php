<?php


namespace Raven\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface
{

    public function __construct($id)
    {
        parent::__construct(sprintf("No entry found for '%s' identifier", $id));
    }
}
