<?php


namespace Raven\Container;

interface InstantiatorInterface
{

    public function resolveConstructor(string $classname, array $parameters = []);
}
