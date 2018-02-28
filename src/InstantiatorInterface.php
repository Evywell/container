<?php


namespace Raven\Container;

interface InstantiatorInterface
{

    public function resolve(string $classname, array $parameters = []);
}
