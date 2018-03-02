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

    /**
     * Add parameters in the container
     * Format example :
     * $parameters = ['id_parameter' => 'value_parameter', 'second_id_parameter' => 'second_value_parameter', ...]
     *
     * @param array $parameters
     */
    public function resolveConfigurationParameters(array $parameters)
    {
        foreach ($parameters as $key => $parameter) {
            $this->container->addParameter($key, $parameter);
        }
    }

    /**
     * @param array $definitions
     */
    public function resolveConfigurationDefinitions(array $definitions)
    {
        foreach ($definitions as $id => $definition) {
            if (!is_array($definition)) {
                $this->container->addDefinition($id, $definition);
                continue;
            }
            $abstract = isset($definition[0]) ? $definition[0] : null;
            $parameters = isset($definition[1]) ? $definition[1] : [];

            $this->container->addDefinition($id, $abstract, $parameters);
        }
    }

}