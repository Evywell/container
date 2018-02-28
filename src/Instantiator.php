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

    public function resolve(string $classname, array $parameters = [], bool $recursive = true)
    {
        $reflection = new \ReflectionClass($classname);
        if (!$reflection->isInstantiable()) {
            return false;
        }

        // Si ya un constructeur
        if ($constructor = $reflection->getConstructor()) {
            $constructorParameters = $constructor->getParameters();
            // On parcours les paramètres du constructeur
            $constructorParameters = array_map(
                function (\ReflectionParameter $parameter) use ($parameters, $recursive, $classname) {
                    $parameterName = $parameter->getName();
                    // Si on a le paramètre dans le tableau envoyé à cette méthode
                    if (array_key_exists($parameterName, $parameters)) {
                        return $parameters[$parameterName];
                        // Si une valeur par défaut est disponible pour le paramètre
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                        // Si le paramètre est une classe, on tente de l'instancier
                    } elseif ($recursive && class_exists($type = (string) $parameter->getType())) {
                        // Si le container possède cette instance
                        if ($this->container->has($type)) {
                            return $this->container->get($type);
                        }
                    }

                    // On ne sait pas comment résoudre le paramètre
                    throw new InstantiatorException(
                        sprintf("The parameter %s in %s cannot be resolved !", $parameterName, $classname)
                    );
                },
                $constructorParameters
            );

            return $reflection->newInstanceArgs($constructorParameters);
        }

        return $reflection->newInstance();
    }
}
