<?php


namespace Raven\Container;

use Psr\Container\ContainerInterface;
use Raven\Container\Exception\InstantiatorException;

class Instantiator implements InstantiatorInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolveConstructor(string $classname, array $parameters = [], bool $recursive = true)
    {
        $reflection = new \ReflectionClass($classname);
        if (!$reflection->isInstantiable()) {
            return false;
        }

        // S'il y a un constructeur
        if ($constructor = $reflection->getConstructor()) {
            $constructorParameters = $this->resolveMethodParameters($constructor, $parameters, $recursive);
            return $reflection->newInstanceArgs($constructorParameters);
        }

        return $reflection->newInstance();
    }

    public function resolveMethod(string $method, $class, array $parameters = [], $recursive = true)
    {
        if (is_string($class)) {
            $class = $this->resolveConstructor($class, $parameters, $recursive);
        }

        $reflectionMethod = new \ReflectionMethod($class, $method);
        // La méthode comporte des paramètres
        if ($reflectionMethod->getNumberOfParameters() > 0) {
            $methodParameters = $this->resolveMethodParameters($reflectionMethod, $parameters, $recursive);
            return $reflectionMethod->invokeArgs($class, $methodParameters);
        }

        return $reflectionMethod->invoke($class);
    }

    private function resolveMethodParameters(\ReflectionMethod $method, array $parameters = [], $recursive = true): array
    {
        $methodParameters = $method->getParameters();
        // On parcours les paramètres de la méthode
        $methodParameters = array_map(
            function (\ReflectionParameter $parameter) use ($parameters, $recursive, $method) {
                $parameterName = $parameter->getName();
                // Si on a le paramètre dans le tableau envoyé à cette méthode
                if (array_key_exists($parameterName, $parameters)) {
                    return $parameters[$parameterName];
                    // Si une valeur par défaut est disponible pour le paramètre
                } elseif ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                    // Si le paramètre est une classe, on tente de l'instancier
                } elseif ($recursive &&
                    (
                        class_exists($type = (string) $parameter->getType()) ||
                        interface_exists($type = (string) $parameter->getType())
                    )
                ) {
                    // Si le container possède cette instance
                    if ($this->container->has($type)) {
                        return $this->container->get($type);
                    }
                }

                // On ne sait pas comment résoudre le paramètre
                throw new InstantiatorException(
                    sprintf("The parameter %s in %s cannot be resolved !", $parameterName, $method->getName())
                );
            },
            $methodParameters
        );

        return $methodParameters;
    }
}
