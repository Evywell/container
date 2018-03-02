<?php


namespace Raven\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Raven\Container\Exception\ContainerException;
use Raven\Container\Exception\ContainerNotFoundException;
use Raven\Container\Exception\ContainerParameterException;

class Container implements ContainerInterface
{

    const DEFINITION_DEFAULT = '__DEFAULT';
    const DEFINITION_PARAMETER = '__PARAMETER';

    private $definitions;
    private $instances;
    private $instantiator;
    private $aliases;

    public function __construct(?InstantiatorInterface $instantiator = null)
    {
        $this->definitions = [];
        $this->instances = [];
        $this->aliases = [];
        // Initialisation des types de définition par défaut
        $this->definitions[self::DEFINITION_DEFAULT] = [];
        $this->definitions[self::DEFINITION_PARAMETER] = [];
        $this->instantiator = $instantiator ?? new Instantiator($this);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        $definition = $this->getDefinition($id);
        // Si la définition est déjà résolu, on renvoie l'instance
        if ($definition->isResolved()) {
            return $this->instances[self::DEFINITION_DEFAULT][$definition->getId()];
        }
        // On résout l'instance
        return $this->resolve($definition, self::DEFINITION_DEFAULT);
    }

    /**
     * Find the parameter of the container by its identifier and returns it.
     *
     * @param string $id
     * @return array|mixed
     */
    public function getParameter(string $id)
    {
        $definition = $this->getDefinition($id, self::DEFINITION_PARAMETER);
        return $this->resolveParameter($definition);
    }

    /**
     * Add a new entry in the container.
     *
     * @param string $id
     * @param $entry
     * @param array $parameters
     * @param string $type
     * @return Definition
     */
    public function addDefinition(
        string $id,
        $entry,
        array $parameters = [],
        string $type = self::DEFINITION_DEFAULT
    ): Definition {
        $definition = new Definition($id, $entry, $parameters);
        $this->definitions[$type][$id] = $definition;

        return $definition;
    }

    /**
     * Add a new parameter in the container.
     *
     * @param string $id
     * @param $parameter
     */
    public function addParameter(string $id, $parameter): void
    {
        $this->addDefinition($id, $parameter, [], self::DEFINITION_PARAMETER);
    }

    /**
     * Finds a definition by its identifier.
     *
     * @param string $id
     * @param string $type
     * @return Definition
     * @throws ContainerNotFoundException
     */
    public function getDefinition(string $id, string $type = self::DEFINITION_DEFAULT): Definition
    {
        // Si un alias porte cet id, on le prend en priorité
        if ($this->isAlias($id)) {
            return $this->aliases[$id];
        }
        // Si le container contient une définition avec cet id
        if ($this->exists($id, $type)) {
            return $this->definitions[$type][$id];
        }
        // Aucune définition n'a été trouvée
        throw new ContainerNotFoundException($id);
    }

    /**
     * Finds all definitions identified by the given key.
     *
     * @param string $key
     * @return mixed
     * @throws ContainerException
     */
    public function getDefinitions(string $key = self::DEFINITION_DEFAULT)
    {
        if ($this->keyExists($key)) {
            return $this->definitions[$key];
        }
        throw new ContainerException(sprintf("The key %s does not exist", $key));
    }

    /**
     * Get all definition keys
     *
     * @return array
     */
    public function getDefinitionKeys(): array
    {
        return array_keys($this->definitions);
    }

    /**
     * Returns definitions with the given tag
     *
     * @param  string $tag
     * @return Definition[]
     */
    public function findWithTag(string $tag): array
    {
        $definitions = [];
        array_walk_recursive(
            $this->definitions,
            function (Definition $definition) use ($tag, &$definitions) {
                if ($definition->hasTag($tag)) {
                    $definitions[] = $definition;
                }
            }
        );

        return $definitions;
    }

    /**
     * Returns identifiers of definition with the given tag
     *
     * @param  string $tag
     * @return array
     */
    public function getIdsWithTag(string $tag): array
    {
        return array_reduce(
            $this->findWithTag($tag),
            function ($carry, Definition $definition) {
                array_push($carry, $definition->getId());
                return $carry;
            },
            []
        );
    }

    /**
     * Add a new definition key in the container
     *
     * @param  string $key
     * @return void
     */
    public function addDefinitionKey(string $key): void
    {
        if (!$this->keyExists($key)) {
            $this->definitions[$key] = [];
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        // Fonction has
        if (strpos($name, 'has') === 0) {
            $parameter = strtolower(mb_substr($name, 3));
            if ($this->keyExists($parameter)) {
                return call_user_func_array([$this, 'exists'], $arguments);
            }
        } elseif (strpos($name, 'get') === 0) {
            $parameter = strtolower(mb_substr($name, 3));
            if ($this->keyExists($parameter)) {
                $definition = (call_user_func_array([$this, 'getDefinition'], [$arguments[0], $parameter]));
                return $this->resolve($definition);
            }
        }

        trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->exists($id);
    }

    /**
     * Returns true if the container has a parameter for the given identifier
     * Returns false otherwise.
     *
     * @param  string $id
     * @return bool
     */
    public function hasParameter(string $id): bool
    {
        return $this->exists($id, self::DEFINITION_PARAMETER);
    }

    /**
     * Returns true if the container has an entry with the type for the given identifier
     * Returns false otherwise.
     *
     * @param  string $id
     * @param  string $type
     * @return bool
     */
    public function exists(string $id, string $type = self::DEFINITION_DEFAULT): bool
    {
        return array_key_exists($id, $this->definitions[$type]);
    }

    /**
     * Bind a tag to a Definition
     *
     * @param  string     $alias
     * @param  Definition $definition
     * @throws ContainerException
     */
    public function addAlias(string $alias, Definition $definition)
    {
        if ($this->isAlias($alias)) {
            throw new ContainerException(sprintf("The alias %s already exists", $alias));
        }
        $this->aliases[$alias] = $definition;
    }

    /**
     * Returns true if the given identifier is an alias
     * Returns false otherwise.
     *
     * @param  string $id
     * @return bool
     */
    public function isAlias(string $id)
    {
        return array_key_exists($id, $this->aliases);
    }

    /**
     * Returns true if the container has the given key
     * Returns false otherwise.
     *
     * @param  string $key
     * @return bool
     */
    private function keyExists(string $key)
    {
        return array_key_exists($key, $this->definitions);
    }

    /**
     * Resolves a parameter like %param%
     *
     * @param  Definition $parameterDefinition
     * @return array|mixed
     */
    private function resolveParameter(Definition $parameterDefinition)
    {
        // On regarde si la définition est déjà résolue
        if ($parameterDefinition->isResolved()) {
            return $this->instances[self::DEFINITION_PARAMETER][$parameterDefinition->getId()];
        }
        $entry = $parameterDefinition->getEntry();
        $parameter = $this->parseParameter($entry);

        $this->instances[self::DEFINITION_PARAMETER][$parameterDefinition->getId()] = $parameter;

        // On marque la définition comme résolue
        $parameterDefinition->setResolved(true);
        return $parameter;
    }

    /**
     * Converts a parameter like %param% with the parameter value
     *
     * @param  $parameter
     * @return array|mixed
     * @throws ContainerParameterException
     */
    private function parseParameter($parameter)
    {
        // Si c'est un tableau de paramètre, on les résout tous
        if (is_array($parameter)) {
            return array_map([$this, 'parseParameter'], $parameter);
        }
        // Si c'est une chaine de caractères...
        if (is_string($parameter)) {
            // ...qui contient simplement un paramètre formé de %param%
            if (preg_match("#^%([\w\.]+)%$#", $parameter, $matches)) {
                $param_name = $matches[1];
                // On le cherche dans le tableau de paramètre
                if ($this->hasParameter($param_name)) {
                    // Si on le trouve, on le retourne
                    return $this->getParameter($param_name);
                }
                // Le paramètre n'existe pas
                throw new ContainerParameterException(
                    sprintf("The given parameter %s does not exist", $parameter)
                );
            }
            // S'il contient plusieurs paramètres, on les remplacent par leur valeur
            $parameter = preg_replace_callback(
                "#%([\w\.]+)%#",
                function ($matches) use ($parameter) {
                    // On le cherche dans le tableau de paramètres
                    if ($this->hasParameter($matches[1])) {
                        // Si on la trouvé, on le retourne
                        return $this->getParameter($matches[1]);
                    }
                    // Le paramètre n'existe pas
                    throw new ContainerParameterException(
                        sprintf("The given parameter %s does not exist", $parameter)
                    );
                },
                $parameter
            );
        }

        return $parameter;
    }

    /**
     * Resolves a Definition
     *
     * @param  Definition $definition
     * @param  string     $type
     * @return bool|mixed|object
     * @throws ContainerException
     */
    private function resolve(Definition $definition, $type = self::DEFINITION_DEFAULT)
    {
        $entry = $definition->getEntry();
        $definitionParameters = $this->parseParameter($definition->getParameters());
        $instance = $entry;
        // On regarde si la définition est une closure
        if (is_callable($entry) || $entry instanceof \Closure) {
            $instance = call_user_func_array($entry, $definitionParameters);
        }

        // On veut résoudre une instance
        if (is_string($entry)) {
            // Si le string est une classe
            if (class_exists($entry)) {
                // On tente de résoudre la classe
                if (!$instance = $this->instantiator->resolve($entry, $definitionParameters)) {
                    throw new ContainerException(sprintf("%s is not a valid class", $entry));
                }
            }
        }

        // Si la définition n'est pas une factory, on sauvegarde son instance
        if (!$definition->isFactory()) {
            $definition->setResolved(true);
            $this->instances[$type][$definition->getId()] = $instance;
        }

        return $instance;
    }
}
