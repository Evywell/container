<?php


namespace Tests;


use PHPUnit\Framework\TestCase;
use Raven\Container\Container;
use Tests\Classes\SimpleClass;

class ContainerTagTest extends TestCase
{

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testFindWithAlias()
    {
        $this->container->addDefinitionKey('something');
        $def = $this->container->addDefinition(SimpleClass::class, SimpleClass::class, [], 'something');
        $def->addTag('mytag');
        $definitions = $this->container->findWithTag('mytag');
        $this->assertCount(1, $definitions);
    }

    public function testFindIdsWithAlias()
    {
        $this->container->addDefinitionKey('something');
        $def = $this->container->addDefinition(SimpleClass::class, SimpleClass::class, [], 'something');
        $def->addTag('mytag');
        $ids = $this->container->getIdsWithTag('mytag');
        $this->assertCount(1, $ids);
        $this->assertEquals(SimpleClass::class, $ids[0]);
    }

}