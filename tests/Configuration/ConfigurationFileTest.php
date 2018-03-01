<?php

namespace Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Raven\Container\Configuration\ConfigurationResolver;
use Raven\Container\Container;

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


}