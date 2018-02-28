<?php


namespace Tests;


use PHPUnit\Framework\TestCase;
use Raven\Container\Container;
use Tests\Classes\SimpleClass;

class ContainerAliasTest extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testAddAlias()
    {
        $definition = $this->container->addDefinition(SimpleClass::class, SimpleClass::class);
        $this->container->addAlias('simple', $definition);
        $this->assertInstanceOf(SimpleClass::class, $this->container->get('simple'));
    }

}