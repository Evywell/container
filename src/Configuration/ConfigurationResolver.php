<?php


namespace Raven\Container\Configuration;


use Raven\Container\Container;

class ConfigurationResolver
{

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolveConfigurationParameters(array $parameters)
    {
        foreach ($parameters as $key => $parameter) {
            $this->container->addParameter($key, $parameter);
        }
    }

}