<?php

namespace Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Raven\Container\Configuration\ConfigurationResolver;
use Raven\Container\Container;
use Tests\Classes\ConstructorClass;
use Tests\Classes\ConstructorClassThree;
use Tests\Classes\ConstructorClassTwo;
use Tests\Classes\SimpleClass;
use Tests\Classes\SimpleInterface;

class ConfigurationFileTest extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ConfigurationResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->container = new Container();
        $this->resolver = new ConfigurationResolver($this->container);
    }

    public function testResolveParameters()
    {
        $parameters = require 'parameters.php';
        $this->resolver->resolveConfigurationParameters($parameters);
        $firstKey = $this->container->getParameter('firstKey');
        $this->assertEquals(3, $firstKey);
        $keyArray = $this->container->getParameter('keyArray');
        $this->assertEquals(['first_element', 'key' => 'value', 13, 54, ['other_array']], $keyArray);
        $myValue = $this->container->getParameter('myValue');
        $this->assertEquals(3, $myValue);
        $myComplexeValue = $this->container->getParameter('myComplexeValue');
        $this->assertEquals(['element' => ['other_array' => 'super-3']], $myComplexeValue);
        $mySecondComplexeValue = $this->container->getParameter('mySecondComplexeValue');
        $this->assertEquals([['element' => ['other_array' => 'super-3']], ['first_element', 'key' => 'value', 13, 54, ['other_array']]], $mySecondComplexeValue);
    }

    public function testResolveDefinitions()
    {
        $definitions = require 'definitions.php';
        $this->resolver->resolveConfigurationDefinitions($definitions);
        $simple = $this->container->get('simple');
        $this->assertInstanceOf(SimpleClass::class, $simple);
        $simpleInterface = $this->container->get(SimpleInterface::class);
        $this->assertInstanceOf(SimpleClass::class, $simpleInterface);
        /** @var ConstructorClass $constructor */
        $constructor = $this->container->get('constructor');
        $this->assertInstanceOf(ConstructorClass::class, $constructor);
        $this->assertEquals('hello world', $constructor->getSentence());
        $this->assertEquals('myArg', $constructor->getArg());

        /** @var ConstructorClassTwo $constructorTwo */
        $constructorTwo = $this->container->get(ConstructorClassTwo::class);
        $this->assertInstanceOf(ConstructorClassTwo::class, $constructorTwo);
        $this->assertEquals('hello world', call_user_func($constructorTwo->getCallback()));

        /** @var ConstructorClassThree $constructorThree */
        $constructorThree = $this->container->get(ConstructorClassThree::class);
        $this->assertInstanceOf(ConstructorClassThree::class, $constructorThree);
    }

    public function testResolveDefinitionsWithAlias()
    {
        $definitions = require 'definitions.php';
        $this->resolver->resolveConfigurationDefinitions($definitions);

        $simple = $this->container->get('simpleWithAlias');
        $this->assertInstanceOf(SimpleClass::class, $simple);

        $alias = $this->container->get('simpleAlias');
        $this->assertInstanceOf(SimpleClass::class, $alias);

    }


}